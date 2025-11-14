<?php

namespace Drupal\beta_tender\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for uploading scanned images with dateline information.
 */
class UploadScannedImagesForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an UploadScannedImagesForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'beta_tender_upload_scanned_images';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['dateline'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dateline Information'),
      '#description' => $this->t('Specify the source and date for the uploaded images.'),
    ];

    // Source field.
    $form['dateline']['source'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Source'),
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => [
        'target_bundles' => ['tender_source'],
      ],
      '#required' => TRUE,
      '#description' => $this->t('Select or enter the source (e.g., newspaper name).'),
    ];

    // Publish date field.
    $form['dateline']['publish_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Publish Date'),
      '#required' => TRUE,
      '#description' => $this->t('Enter the publication date from the source.'),
      '#default_value' => date('Y-m-d'),
    ];

    // File upload field.
    $form['files'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Scanned Images'),
      '#description' => $this->t('Upload one or more scanned images.'),
      '#upload_location' => 'public://scanned-images',
      '#multiple' => TRUE,
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg jpeg png gif pdf'],
        'file_validate_size' => [25600000], // 25MB
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $source_id = $form_state->getValue('source');
    $publish_date = $form_state->getValue('publish_date');
    $file_ids = $form_state->getValue('files');

    if (empty($file_ids)) {
      $this->messenger()->addError($this->t('No files were uploaded.'));
      return;
    }

    $media_storage = $this->entityTypeManager->getStorage('media');
    $file_storage = $this->entityTypeManager->getStorage('file');
    
    $created_count = 0;
    
    foreach ($file_ids as $file_id) {
      $file = $file_storage->load($file_id);
      if (!$file) {
        continue;
      }

      // Make the file permanent.
      $file->setPermanent();
      $file->save();

      // Create a scanned image media entity.
      $media = $media_storage->create([
        'bundle' => 'scanned_image',
        'name' => $file->getFilename(),
        'field_media_image' => [
          'target_id' => $file->id(),
        ],
        'field_media_source' => [
          'target_id' => $source_id,
        ],
        'field_processed_status' => FALSE,
      ]);

      // Store the publish date for later use when creating tenders.
      if ($media->hasField('field_publish_date')) {
        $media->set('field_publish_date', $publish_date);
      }

      $media->save();
      $created_count++;
    }

    if ($created_count > 0) {
      $this->messenger()->addStatus($this->t('Successfully uploaded @count scanned image(s).', [
        '@count' => $created_count,
      ]));
    }
    else {
      $this->messenger()->addWarning($this->t('No images were created.'));
    }
  }

}
