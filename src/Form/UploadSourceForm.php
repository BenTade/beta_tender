<?php

namespace Drupal\beta_tender\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\File\FileSystemInterface;

/**
 * Form for uploading source media and creating tenders.
 */
class UploadSourceForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'beta_tender_upload_source_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['upload_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Upload New Sources'),
      '#open' => TRUE,
    ];

    // Prepare upload directory: public://tender_media/November 26, 2025
    $date_folder = date('F-d-Y');
    $directory = 'public://tender_media/' . $date_folder;
    \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    $form['upload_wrapper']['source_files'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Source Files'),
      '#description' => $this->t('Allowed extensions: jpg jpeg png pdf. Multiple files allowed.'),
      '#upload_location' => $directory,
      '#multiple' => TRUE,
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'jpg jpeg png pdf'],
      ],
    ];

    $form['upload_wrapper']['upload_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload and Save as Media'),
      '#submit' => ['::uploadSubmit'],
    ];

    // List of unassigned media.
    $form['media_list'] = [
      '#type' => 'table',
      '#header' => [
        'drag' => '', // Placeholder for drag handle
        'select' => $this->t('Select'),
        'name' => $this->t('Name'),
        'folder' => $this->t('Folder'),
        'type' => $this->t('Type'),
        'created' => $this->t('Created'),
        'weight' => $this->t('Weight'),
        'parent' => $this->t('Parent'),
      ],
      '#empty' => $this->t('No unassigned source media found in tender_media folder.'),
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'media-parent',
          'subgroup' => 'media-parent',
          'source' => 'media-id',
          'hidden' => TRUE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'media-order-weight',
        ],
      ],
    ];

    $media_items = $this->getUnassignedMediaItems();
    
    foreach ($media_items as $id => $item) {
      /** @var \Drupal\media\MediaInterface $media */
      $media = $item['media'];
      $folder = $item['folder'];

      $form['media_list'][$id]['#attributes']['class'][] = 'draggable';
      
      // Drag handle column with ID field.
      $form['media_list'][$id]['drag'] = [
        '#markup' => '',
        'id' => [
          '#type' => 'hidden',
          '#default_value' => $id,
          '#attributes' => ['class' => ['media-id']],
        ],
      ];

      $form['media_list'][$id]['select'] = [
        '#type' => 'checkbox',
        '#default_value' => FALSE,
      ];
      $form['media_list'][$id]['name'] = [
        '#markup' => $media->getName(),
      ];
      $form['media_list'][$id]['folder'] = [
        '#markup' => $folder,
      ];
      $form['media_list'][$id]['type'] = [
        '#markup' => $media->bundle(),
      ];
      $form['media_list'][$id]['created'] = [
        '#markup' => \Drupal::service('date.formatter')->format($media->getCreatedTime(), 'short'),
      ];
      $form['media_list'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $media->getName()]),
        '#title_display' => 'invisible',
        '#default_value' => 0,
        '#attributes' => ['class' => ['media-order-weight']],
      ];
      $form['media_list'][$id]['parent'] = [
        '#type' => 'hidden',
        '#default_value' => '',
        '#attributes' => ['class' => ['media-parent']],
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['create_tender'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Tender from Selected'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Custom submit handler for uploading files.
   */
  public function uploadSubmit(array &$form, FormStateInterface $form_state) {
    $fids = $form_state->getValue('source_files');
    if (!empty($fids)) {
      $files = File::loadMultiple($fids);
      $count = 0;
      foreach ($files as $file) {
        $media_type = 'document';
        $mime = $file->getMimeType();
        if (strpos($mime, 'image/') === 0) {
          $media_type = 'image';
        }

        // Create Media entity.
        $media_data = [
          'bundle' => $media_type,
          'uid' => \Drupal::currentUser()->id(),
          'status' => 1,
          'name' => $file->getFilename(),
        ];

        if ($media_type == 'image') {
          $media_data['field_media_image'] = [
            'target_id' => $file->id(),
            'alt' => $file->getFilename(),
          ];
        } else {
          $media_data['field_media_document'] = [
            'target_id' => $file->id(),
          ];
        }

        $media = Media::create($media_data);
        $media->save();
        $count++;
        
        // Make the file permanent.
        $file->setPermanent();
        $file->save();
      }
      $this->messenger()->addStatus($this->t('@count source media items created.', ['@count' => $count]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This handles the "Create Tender" action.
    $media_list = $form_state->getValue('media_list');
    
    // Filter selected items.
    $selected_media = [];
    foreach ($media_list as $id => $item) {
      if (!empty($item['select'])) {
        $selected_media[$id] = $item;
      }
    }
    
    if (empty($selected_media)) {
      $this->messenger()->addWarning($this->t('No media selected.'));
      return;
    }

    // Sort by weight.
    uasort($selected_media, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });

    // Extract IDs.
    $media_ids = array_keys($selected_media);

    // Create a new Tender node.
    $node = Node::create([
      'type' => 'tender',
      'title' => 'New Tender - ' . date('Y-m-d H:i'),
      'field_source_media' => $media_ids, // Attach selected media.
      'uid' => \Drupal::currentUser()->id(),
      'status' => 0, // Draft by default
    ]);
    $node->save();

    $this->messenger()->addStatus($this->t('Tender @title created with @count source files.', [
      '@title' => $node->toLink()->toString(),
      '@count' => count($selected_media),
    ]));
    
    // Redirect to the edit form of the new tender.
    $form_state->setRedirect('entity.node.edit_form', ['node' => $node->id()]);
  }

  /**
   * Helper to get unassigned media items filtered by folder.
   */
  protected function getUnassignedMediaItems() {
    // Query media of type image/document.
    $query = \Drupal::entityQuery('media')
      ->condition('bundle', ['image', 'document'], 'IN')
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);
      
    // Find media IDs that ARE used in Tenders.
    $used_media_query = \Drupal::entityQuery('node')
      ->condition('type', 'tender')
      ->condition('field_source_media', NULL, 'IS NOT NULL')
      ->accessCheck(FALSE);
    $tenders = $used_media_query->execute();
    
    $used_media_ids = [];
    if (!empty($tenders)) {
      $nodes = Node::loadMultiple($tenders);
      foreach ($nodes as $node) {
        if (!$node->hasField('field_source_media')) continue;
        $field_items = $node->get('field_source_media')->getValue();
        foreach ($field_items as $item) {
          $used_media_ids[] = $item['target_id'];
        }
      }
    }
    
    if (!empty($used_media_ids)) {
      $query->condition('mid', $used_media_ids, 'NOT IN');
    }

    $mids = $query->execute();
    $medias = Media::loadMultiple($mids);

    $items = [];
    foreach ($medias as $media) {
      // Get file entity.
      $file = NULL;
      if ($media->bundle() == 'image' && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
      } elseif ($media->bundle() == 'document' && !$media->get('field_media_document')->isEmpty()) {
        $file = $media->get('field_media_document')->entity;
      }

      if ($file) {
        $uri = $file->getFileUri();
        // Check if it is in tender_media.
        if (strpos($uri, 'public://tender_media/') === 0) {
           // Extract subfolder.
           // public://tender_media/November 26, 2025/filename.jpg
           $relative = substr($uri, strlen('public://tender_media/'));
           $parts = explode('/', $relative);
           $folder = count($parts) > 1 ? $parts[0] : 'Root';
           
           $items[$media->id()] = [
             'media' => $media,
             'folder' => $folder,
           ];
        }
      }
    }

    // Sort by folder (descending) then created date.
    uasort($items, function($a, $b) {
      if ($a['folder'] != $b['folder']) {
        // Try to parse date for better sorting.
        $time_a = strtotime($a['folder']);
        $time_b = strtotime($b['folder']);
        if ($time_a && $time_b) {
          return $time_b <=> $time_a;
        }
        return strcmp($b['folder'], $a['folder']);
      }
      return $b['media']->getCreatedTime() <=> $a['media']->getCreatedTime();
    });

    return $items;
  }

}
