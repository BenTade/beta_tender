<?php

namespace Drupal\beta_tender\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Service for creating tender nodes from scanned images.
 */
class TenderCreationService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Cached text format for tender body fields.
   *
   * @var string|null
   */
  protected $bodyFormat;

  /**
   * Constructs a TenderCreationService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    TimeInterface $time
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('beta_tender');
    $this->time = $time;
  }

  /**
   * Create a tender node from a group of scanned images.
   *
   * @param array $media_entities
   *   Array of media entities (scanned images).
   *
   * @return \Drupal\node\NodeInterface|null
   *   The created tender node, or NULL on failure.
   */
  public function createTenderFromImages(array $media_entities): ?NodeInterface {
    if (empty($media_entities)) {
      $this->logger->error('Cannot create tender: no images provided');
      return NULL;
    }

    try {
      $media_references = [];
      $source_id = NULL;

      foreach ($media_entities as $media) {
        if (!$media instanceof MediaInterface) {
          continue;
        }

        $media_references[] = ['target_id' => $media->id()];

        if ($source_id === NULL && $media->hasField('field_media_source') && !$media->get('field_media_source')->isEmpty()) {
          $source_id = $media->get('field_media_source')->target_id;
        }
      }

      $body_text = $this->buildPlaceholderBody($media_entities);
      $body_summary = $this->buildSummary($body_text);
      $body_format = $this->resolveBodyFormat();
      $title = $this->buildTitle($media_entities);

      // Create the tender node.
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->create([
        'type' => 'tender',
        'title' => $title,
        'field_source_media' => $media_references,
        'field_tender_source' => $source_id ? ['target_id' => $source_id] : [],
        'field_body' => [
          'value' => $body_text,
          'summary' => $body_summary,
          'format' => $body_format,
        ],
        'field_opening_date' => [],
        'field_closing_date' => [],
        'field_tender_categories' => [],
        'field_region' => [],
        'moderation_state' => 'needs_review',
        'status' => 0, // Unpublished by default.
      ]);

      $node->save();

      // Mark all media entities as processed.
      foreach ($media_entities as $media) {
        if ($media instanceof MediaInterface && $media->hasField('field_processed_status')) {
          $media->set('field_processed_status', TRUE);
          $media->save();
        }
      }

      $this->logger->info('Created tender node @nid from @count media item(s)', [
        '@nid' => $node->id(),
        '@count' => count($media_entities),
      ]);

      return $node;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to create tender: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
  /**
   * Build a human-readable title based on the provided media entities.
   */
  protected function buildTitle(array $media_entities): string {
    foreach ($media_entities as $media) {
      if ($media instanceof MediaInterface && $media->label()) {
        $title = $media->label();
        return mb_strimwidth($title, 0, 255, '');
      }
    }

    return 'Tender created ' . date('Y-m-d H:i', $this->time->getRequestTime());
  }

  /**
   * Build placeholder body text listing the associated media.
   */
  protected function buildPlaceholderBody(array $media_entities): string {
    $names = [];
    foreach ($media_entities as $media) {
      if ($media instanceof MediaInterface && $media->label()) {
        $names[] = $media->label();
      }
    }

    $media_line = $names ? implode(', ', $names) : $this->t('No media titles available');

    return (string) $this->t('This tender was generated from uploaded media and still needs manual content entry. Associated media: @media_list.', [
      '@media_list' => $media_line,
    ]);
  }

  /**
   * Build a required summary for the tender body field.
   */
  protected function buildSummary(string $text): string {
    $normalized = trim(preg_replace('/\s+/', ' ', $text));
    if ($normalized === '') {
      return (string) $this->t('Summary auto-generated from uploaded media. Please refine during proofreading.');
    }

    $max_length = 600;
    if (mb_strlen($normalized) <= $max_length) {
      return $normalized;
    }

    return rtrim(mb_substr($normalized, 0, $max_length - 3)) . '...';
  }

  /**
   * Resolve the preferred text format for tender body fields.
   */
  protected function resolveBodyFormat(): string {
    if ($this->bodyFormat !== NULL) {
      return $this->bodyFormat;
    }

    $format_storage = $this->entityTypeManager->getStorage('filter_format');
    if ($format_storage && $format_storage->load('content')) {
      $this->bodyFormat = 'content';
    }
    else {
      $this->bodyFormat = 'plain_text';
    }

    return $this->bodyFormat;
  }

  /**
   * Simple translation helper proxy.
   */
  protected function t(string $string, array $context = []): string {
    return \Drupal::translation()->translate($string, $context);
  }

}
