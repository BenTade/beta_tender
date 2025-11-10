<?php

namespace Drupal\beta_tender\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\beta_tender\Service\TenderBatchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for arranging images with tabledrag.
 */
class ImageArrangementForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The tender batch service.
   *
   * @var \Drupal\beta_tender\Service\TenderBatchService
   */
  protected $batchService;

  /**
   * Constructs an ImageArrangementForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\beta_tender\Service\TenderBatchService $batch_service
   *   The tender batch service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    TenderBatchService $batch_service
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->batchService = $batch_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('beta_tender.batch_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'beta_tender_image_arrangement';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $date = NULL, $source_id = NULL, $media_ids = NULL) {
    // Store parameters for submit handler.
    $form_state->set('date', $date);
    $form_state->set('source_id', $source_id);
    
    if (empty($media_ids)) {
      return $form;
    }

    $media_storage = $this->entityTypeManager->getStorage('media');
    $media_entities = $media_storage->loadMultiple($media_ids);

    $form['#tree'] = TRUE;
    
    $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Drag images to group them together. Indent images under a parent to create multi-image tenders. Select parent images to process them.') . '</p>',
    ];

    // Build the tabledrag table.
    $form['images'] = [
      '#type' => 'table',
      '#header' => [
        $this->t(''),
        $this->t('Image'),
        $this->t('Name'),
        $this->t('Created'),
        $this->t('Weight'),
        $this->t('Parent'),
        $this->t('Select'),
      ],
      '#tabledrag' => [
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'image-parent',
          'subgroup' => 'image-parent',
          'source' => 'image-id',
          'hidden' => FALSE,
        ],
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'image-weight',
        ],
      ],
      '#attributes' => [
        'id' => 'image-arrangement-table',
      ],
    ];

    $weight = 0;
    foreach ($media_entities as $media) {
      $media_id = $media->id();
      
      $image_url = '';
      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $file = $media->get('field_media_image')->entity;
        if ($file) {
          $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }

      $form['images'][$media_id]['#attributes']['class'][] = 'draggable';
      
      // Thumbnail.
      $form['images'][$media_id]['thumbnail'] = [
        '#type' => 'markup',
        '#markup' => $image_url ? '<img src="' . $image_url . '" style="max-width: 100px; max-height: 100px;" />' : '',
      ];

      // Name.
      $form['images'][$media_id]['name'] = [
        '#plain_text' => $media->getName(),
      ];

      // Created date.
      $form['images'][$media_id]['created'] = [
        '#plain_text' => \Drupal::service('date.formatter')->format($media->getCreatedTime(), 'short'),
      ];

      // Weight.
      $form['images'][$media_id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => [
          'class' => ['image-weight'],
        ],
      ];

      // Parent.
      $form['images'][$media_id]['parent'] = [
        '#type' => 'select',
        '#title' => $this->t('Parent'),
        '#title_display' => 'invisible',
        '#options' => $this->getParentOptions($media_entities, $media_id),
        '#default_value' => '',
        '#attributes' => [
          'class' => ['image-parent'],
        ],
        '#empty_value' => '',
      ];

      // Hidden field for ID.
      $form['images'][$media_id]['id'] = [
        '#type' => 'hidden',
        '#value' => $media_id,
        '#attributes' => [
          'class' => ['image-id'],
        ],
      ];

      // Checkbox for selection (only for parent images).
      $form['images'][$media_id]['select'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Select'),
        '#title_display' => 'invisible',
      ];

      $weight++;
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process OCR and Create Tenders'),
    ];

    return $form;
  }

  /**
   * Get parent options for the select list.
   *
   * @param array $media_entities
   *   Array of media entities.
   * @param int $current_id
   *   The current media ID.
   *
   * @return array
   *   Array of parent options.
   */
  protected function getParentOptions(array $media_entities, $current_id): array {
    $options = ['' => $this->t('- None -')];
    
    foreach ($media_entities as $media) {
      if ($media->id() != $current_id) {
        $options[$media->id()] = $media->getName();
      }
    }
    
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $images = $form_state->getValue('images');
    
    if (empty($images)) {
      $this->messenger()->addWarning($this->t('No images to process.'));
      return;
    }

    // Build groups based on parent-child relationships.
    $groups = [];
    $selected = [];
    
    foreach ($images as $media_id => $data) {
      if (!empty($data['select'])) {
        $selected[] = $media_id;
      }
    }

    if (empty($selected)) {
      $this->messenger()->addWarning($this->t('Please select at least one image group to process.'));
      return;
    }

    // Build groups: for each selected parent, include its children.
    foreach ($selected as $parent_id) {
      $group = [$parent_id];
      
      // Find children.
      foreach ($images as $media_id => $data) {
        if (!empty($data['parent']) && $data['parent'] == $parent_id) {
          $group[] = $media_id;
        }
      }
      
      $groups[] = $group;
    }

    if (empty($groups)) {
      $this->messenger()->addWarning($this->t('No valid groups to process.'));
      return;
    }

    // Create and set the batch.
    $batch = $this->batchService->createBatch($groups);
    batch_set($batch);
  }

}
