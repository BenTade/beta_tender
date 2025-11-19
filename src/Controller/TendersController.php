<?php

namespace Drupal\beta_tender\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the unified Tenders page.
 */
class TendersController extends ControllerBase {

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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Pager manager service.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * Cached moderation state labels keyed by state ID.
   *
   * @var string[]
   */
  protected $moderationStateLabels = [];

  /**
   * Constructs a TendersController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pager_manager
   *   The pager manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter,
    AccountInterface $current_user,
    PagerManagerInterface $pager_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->currentUser = $current_user;
    $this->pagerManager = $pager_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('current_user'),
      $container->get('pager.manager')
    );
  }

  /**
   * Main Tenders page showing datelines grouped by source.
   *
   * @return array
   *   Render array for the tenders page.
   */
  public function mainPage(): array {
    $build = [
      '#theme' => 'tender_main_page',
      '#attached' => [
        'library' => ['beta_tender/tenders'],
      ],
    ];

    // Add upload form at the top.
    $build['upload_form'] = $this->formBuilder()->getForm('Drupal\beta_tender\Form\UploadScannedImagesForm');

    // Query all tenders.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'tender')
      ->accessCheck(FALSE)
      ->sort('field_publish_date', 'DESC')
      ->sort('created', 'DESC');
    
    $tender_ids = $query->execute();
    
    if (empty($tender_ids)) {
      $build['#empty'] = [
        '#markup' => '<p>' . $this->t('No tenders have been created yet.') . '</p>',
      ];
      return $build;
    }

    $tenders = $node_storage->loadMultiple($tender_ids);
    
    // Group by dateline (source + publish date).
    $datelines = [];
    
    foreach ($tenders as $tender) {
      $source_id = NULL;
      $source_name = $this->t('Unknown Source');
      
      if ($tender->hasField('field_tender_source') && !$tender->get('field_tender_source')->isEmpty()) {
        $source = $tender->get('field_tender_source')->entity;
        if ($source) {
          $source_id = $source->id();
          $source_name = $source->getName();
        }
      }
      
      // Get publish date (newspaper date).
      $publish_date = NULL;
      $date_key = 'unknown';
      if ($tender->hasField('field_publish_date') && !$tender->get('field_publish_date')->isEmpty()) {
        $publish_date = $tender->get('field_publish_date')->value;
        if ($publish_date) {
          $date_key = date('Y-m-d', strtotime($publish_date));
        }
      }
      
      // If no publish date, use created date.
      if ($date_key === 'unknown') {
        $date_key = date('Y-m-d', $tender->getCreatedTime());
      }
      
      // Create dateline key: source_date.
      $dateline_key = $source_id . '_' . $date_key;
      
      if (!isset($datelines[$dateline_key])) {
        $datelines[$dateline_key] = [
          'source_id' => $source_id,
          'source_name' => $source_name,
          'date' => $date_key,
          'publish_date' => $publish_date,
          'tenders' => [],
        ];
      }
      
      $datelines[$dateline_key]['tenders'][] = $tender->id();
    }

    // Sort datelines by date (descending), then by source name.
    usort($datelines, function ($a, $b) {
      // First sort by date (newest first).
      $date_cmp = strcmp($b['date'], $a['date']);
      if ($date_cmp !== 0) {
        return $date_cmp;
      }
      // Then sort by source name.
      return strcmp($a['source_name'], $b['source_name']);
    });

    // Paginate datelines so the list stays manageable.
    $dateline_limit = 20;
    $dateline_element = 0;
    $dateline_pager = $this->pagerManager->createPager(count($datelines), $dateline_limit, $dateline_element);
    $dateline_offset = $dateline_pager->getCurrentPage() * $dateline_limit;
    $datelines = array_slice($datelines, $dateline_offset, $dateline_limit);

    // Prepare dateline data for template.
    $dateline_data = [];
    foreach ($datelines as $dateline) {
      if (empty($dateline['source_id'])) {
        // Cannot build the dateline detail route without a source ID.
        continue;
      }

      $dateline_data[] = [
        'source_id' => $dateline['source_id'],
        'source_name' => $dateline['source_name'],
        'date' => $dateline['date'],
        'formatted_date' => $this->dateFormatter->format(strtotime($dateline['date']), 'custom', 'F j, Y'),
        'tender_count' => count($dateline['tenders']),
        'url' => Url::fromRoute('beta_tender.dateline_detail', [
          'source_id' => $dateline['source_id'],
          'date' => $dateline['date'],
        ])->toString(),
      ];
    }

    $build['#datelines'] = $dateline_data;
    $build['pager'] = [
      '#type' => 'pager',
      '#element' => $dateline_element,
    ];

    return $build;
  }

  /**
   * Dateline detail page showing all tenders for a source and date.
   *
   * @param string $source_id
   *   The source taxonomy term ID.
   * @param string $date
   *   The date in Y-m-d format.
   *
   * @return array
   *   Render array for the dateline detail page.
   */
  public function datelineDetail(string $source_id, string $date): array {
    // Load source term.
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $source = $term_storage->load($source_id);
    
    $source_name = $source ? $source->getName() : $this->t('Unknown Source');
    
    $build = [
      '#theme' => 'tender_dateline_detail',
      '#source_name' => $source_name,
      '#date' => $date,
      '#formatted_date' => $this->dateFormatter->format(strtotime($date), 'custom', 'F j, Y'),
      '#attached' => [
        'library' => ['beta_tender/tenders'],
      ],
    ];

    // Query tenders for this dateline.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'tender')
      ->condition('field_tender_source', $source_id)
      ->accessCheck(FALSE)
      ->sort('created', 'DESC');
    
    $tender_ids = $query->execute();
    
    if (empty($tender_ids)) {
      $build['#empty'] = [
        '#markup' => '<p>' . $this->t('No tenders found for this dateline.') . '</p>',
      ];
      return $build;
    }

    $tenders = $node_storage->loadMultiple($tender_ids);

    // Filter tenders by date and prepare data.
    $tender_list = [];
    foreach ($tenders as $tender) {
      // Check if tender matches the date.
      $tender_date = NULL;
      if ($tender->hasField('field_publish_date') && !$tender->get('field_publish_date')->isEmpty()) {
        $publish_date = $tender->get('field_publish_date')->value;
        if ($publish_date) {
          $tender_date = date('Y-m-d', strtotime($publish_date));
        }
      }
      
      // If no publish date, use created date.
      if (!$tender_date) {
        $tender_date = date('Y-m-d', $tender->getCreatedTime());
      }
      
      if ($tender_date !== $date) {
        continue;
      }
      
      // Get author.
      $author_name = $this->t('Unknown');
      if ($tender->getOwner()) {
        $author_name = $tender->getOwner()->getDisplayName();
      }
      
      // Get assigned editor (proofreader).
      $editor_name = $this->t('Unassigned');
      if ($tender->hasField('field_assigned_editor') && !$tender->get('field_assigned_editor')->isEmpty()) {
        $editor = $tender->get('field_assigned_editor')->entity;
        if ($editor) {
          $editor_name = $editor->getDisplayName();
        }
      }
      
      // Resolve moderation state label to show accurate workflow status.
      $moderation_label = (string) $this->t('Unknown');
      if ($tender->hasField('moderation_state') && !$tender->get('moderation_state')->isEmpty()) {
        $status_value = $tender->get('moderation_state')->value;
        $moderation_label = $this->getModerationStateLabel($status_value);
      }
      
      // Get share/sync status (placeholder for now - Entity Share integration).
      $share_status = $this->t('Not Synced');
      
      $tender_list[] = [
        'id' => $tender->id(),
        'title' => $tender->label(),
        'url' => $tender->toUrl()->toString(),
        'edit_url' => $tender->toUrl('edit-form')->toString(),
        'author' => $author_name,
        'created' => $this->dateFormatter->format($tender->getCreatedTime(), 'short'),
        'changed' => $this->dateFormatter->format($tender->getChangedTime(), 'short'),
        'moderation_state' => $moderation_label,
        'editor' => $editor_name,
        'share_status' => $share_status,
      ];
    }

    $tender_limit = 50;
    $tender_element = 1;
    $tender_pager = $this->pagerManager->createPager(count($tender_list), $tender_limit, $tender_element);
    $tender_offset = $tender_pager->getCurrentPage() * $tender_limit;
    $tender_list = array_slice($tender_list, $tender_offset, $tender_limit);

    $build['#tenders'] = $tender_list;
    $build['pager'] = [
      '#type' => 'pager',
      '#element' => $tender_element,
    ];

    return $build;
  }

  /**
   * Get a translated moderation state label for a given state ID.
   */
  protected function getModerationStateLabel(?string $state_id): string {
    if (!$state_id) {
      return (string) $this->t('Unknown');
    }

    if (!isset($this->moderationStateLabels[$state_id])) {
      $label = ucfirst(str_replace('_', ' ', $state_id));
      if ($this->entityTypeManager->hasDefinition('moderation_state')) {
        $storage = $this->entityTypeManager->getStorage('moderation_state');
        if ($state = $storage->load($state_id)) {
          $label = $state->label();
        }
      }
      $this->moderationStateLabels[$state_id] = $label;
    }

    return $this->moderationStateLabels[$state_id];
  }

}
