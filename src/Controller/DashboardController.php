<?php

namespace Drupal\beta_tender\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the image processing dashboard.
 */
class DashboardController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a DashboardController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * Main dashboard page showing dates and sources.
   *
   * @return array
   *   Render array for the dashboard.
   */
  public function mainDashboard(): array {
    $build = [
      '#theme' => 'tender_dashboard',
      '#attached' => [
        'library' => ['beta_tender/dashboard'],
      ],
    ];

    // Query scanned images grouped by date and source.
    $media_storage = $this->entityTypeManager->getStorage('media');
    
    $query = $media_storage->getQuery()
      ->condition('bundle', 'scanned_image')
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');
    
    $media_ids = $query->execute();
    
    if (empty($media_ids)) {
      $build['#empty'] = [
        '#markup' => '<p>' . $this->t('No scanned images have been uploaded yet.') . '</p>',
      ];
      return $build;
    }

    $media_entities = $media_storage->loadMultiple($media_ids);
    
    // Group by date and source.
    $grouped_data = [];
    
    foreach ($media_entities as $media) {
      $created = $media->getCreatedTime();
      $date = date('Y-m-d', $created);
      
      $source_id = NULL;
      $source_name = $this->t('Unknown Source');
      
      if ($media->hasField('field_media_source') && !$media->get('field_media_source')->isEmpty()) {
        $source = $media->get('field_media_source')->entity;
        if ($source) {
          $source_id = $source->id();
          $source_name = $source->getName();
        }
      }
      
      $processed = FALSE;
      if ($media->hasField('field_processed_status') && !$media->get('field_processed_status')->isEmpty()) {
        $processed = (bool) $media->get('field_processed_status')->value;
      }
      
      if (!isset($grouped_data[$date])) {
        $grouped_data[$date] = [];
      }
      
      if (!isset($grouped_data[$date][$source_id])) {
        $grouped_data[$date][$source_id] = [
          'source_name' => $source_name,
          'source_id' => $source_id,
          'unprocessed' => 0,
          'total' => 0,
        ];
      }
      
      $grouped_data[$date][$source_id]['total']++;
      if (!$processed) {
        $grouped_data[$date][$source_id]['unprocessed']++;
      }
    }

    // Count created tenders by date and source.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $tender_query = $node_storage->getQuery()
      ->condition('type', 'tender')
      ->accessCheck(TRUE);
    
    $tender_ids = $tender_query->execute();
    $tenders = $node_storage->loadMultiple($tender_ids);
    
    $tender_counts = [];
    foreach ($tenders as $tender) {
      $created = $tender->getCreatedTime();
      $date = date('Y-m-d', $created);
      
      $source_id = NULL;
      if ($tender->hasField('field_tender_source') && !$tender->get('field_tender_source')->isEmpty()) {
        $source_id = $tender->get('field_tender_source')->target_id;
      }
      
      if (!isset($tender_counts[$date])) {
        $tender_counts[$date] = [];
      }
      
      if (!isset($tender_counts[$date][$source_id])) {
        $tender_counts[$date][$source_id] = 0;
      }
      
      $tender_counts[$date][$source_id]++;
    }

    // Build the output.
    $dates = [];
    foreach ($grouped_data as $date => $sources) {
      $sources_data = [];
      foreach ($sources as $source_id => $source_data) {
        $tender_count = $tender_counts[$date][$source_id] ?? 0;
        
        $sources_data[] = [
          'name' => $source_data['source_name'],
          'source_id' => $source_id,
          'unprocessed' => $source_data['unprocessed'],
          'created' => $tender_count,
          'url' => $source_id ? "/admin/content/tender/dashboard/{$date}/{$source_id}" : '#',
        ];
      }
      
      $dates[] = [
        'date' => $date,
        'formatted_date' => $this->dateFormatter->format(strtotime($date), 'custom', 'F j, Y'),
        'sources' => $sources_data,
      ];
    }

    $build['#dates'] = $dates;

    return $build;
  }

  /**
   * Image arrangement page for a specific date and source.
   *
   * @param string $date
   *   The date in Y-m-d format.
   * @param \Drupal\taxonomy\TermInterface $source_id
   *   The source taxonomy term.
   *
   * @return array
   *   Render array for the image arrangement page.
   */
  public function imageArrangement(string $date, TermInterface $source_id): array {
    $build = [
      '#theme' => 'tender_image_arrangement',
      '#date' => $date,
      '#source' => $source_id,
      '#attached' => [
        'library' => ['beta_tender/image-arrangement', 'core/drupal.tabledrag'],
      ],
    ];

    // Get created tenders for this date and source.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'tender')
      ->condition('field_tender_source', $source_id->id())
      ->accessCheck(TRUE)
      ->sort('created', 'DESC');
    
    $tender_ids = $query->execute();
    $tenders = $node_storage->loadMultiple($tender_ids);
    
    $created_tenders = [];
    foreach ($tenders as $tender) {
      $created = $tender->getCreatedTime();
      $tender_date = date('Y-m-d', $created);
      
      if ($tender_date === $date) {
        $created_tenders[] = [
          'id' => $tender->id(),
          'title' => $tender->label(),
          'url' => $tender->toUrl()->toString(),
        ];
      }
    }

    // Get unprocessed images for this date and source.
    $media_storage = $this->entityTypeManager->getStorage('media');
    $media_query = $media_storage->getQuery()
      ->condition('bundle', 'scanned_image')
      ->condition('field_media_source', $source_id->id())
      ->condition('field_processed_status', FALSE)
      ->accessCheck(TRUE)
      ->sort('created', 'ASC');
    
    $media_ids = $media_query->execute();
    $media_entities = $media_storage->loadMultiple($media_ids);
    
    $unprocessed_images = [];
    foreach ($media_entities as $media) {
      $created = $media->getCreatedTime();
      $media_date = date('Y-m-d', $created);
      
      if ($media_date === $date) {
        $image_url = '';
        if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file) {
            $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }
        
        $unprocessed_images[] = [
          'id' => $media->id(),
          'name' => $media->getName(),
          'image_url' => $image_url,
          'created' => $this->dateFormatter->format($media->getCreatedTime(), 'short'),
        ];
      }
    }

    $build['#created_tenders'] = $created_tenders;
    $build['#unprocessed_images'] = $unprocessed_images;

    // Add the tabledrag form.
    if (!empty($unprocessed_images)) {
      $build['arrangement_form'] = $this->formBuilder()->getForm('Drupal\beta_tender\Form\ImageArrangementForm', $date, $source_id->id(), $media_ids);
    }
    else {
      $build['#no_images'] = [
        '#markup' => '<p>' . $this->t('No unprocessed images found for this date and source.') . '</p>',
      ];
    }

    return $build;
  }

}
