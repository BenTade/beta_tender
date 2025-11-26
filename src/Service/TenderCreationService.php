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
   * The OCR service.
   *
   * @var \Drupal\beta_tender\Service\OcrService
   */
  protected $ocrService;

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
   * @param \Drupal\beta_tender\Service\OcrService $ocr_service
   *   The OCR service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    OcrService $ocr_service,
    LoggerChannelFactoryInterface $logger_factory,
    TimeInterface $time
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->ocrService = $ocr_service;
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
      // Extract OCR text from all images.
      $ocr_texts = [];
      $media_references = [];
      $source_id = NULL;

      foreach ($media_entities as $media) {
        if (!$media instanceof MediaInterface) {
          continue;
        }

        // Get the image file.
        if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
          $file = $media->get('field_media_image')->entity;
          if ($file) {
            $ocr_text = $this->ocrService->extractText($file);
            if (!empty($ocr_text)) {
              $ocr_texts[] = $ocr_text;
            }
          }
        }

        // Always reference the source media entity on the tender node.
        $media_references[] = ['target_id' => $media->id()];

        // Get the source from the first media entity.
        if ($source_id === NULL && $media->hasField('field_media_source') && !$media->get('field_media_source')->isEmpty()) {
          $source_id = $media->get('field_media_source')->target_id;
        }
      }

      // Concatenate all OCR texts.
      $full_ocr_text = implode("\n\n", $ocr_texts);

      // Parse the OCR text to extract tender information.
      $tender_data = $this->parseTenderData($full_ocr_text);

      // Create the tender node.
      $body_format = $this->resolveBodyFormat();
      $body_summary = $this->buildSummary($full_ocr_text);
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->create([
        'type' => 'tender',
        'title' => $tender_data['title'],
        'field_source_media' => $media_references,
        'field_tender_source' => $source_id ? ['target_id' => $source_id] : [],
        'field_body' => [
          'value' => $full_ocr_text,
          'summary' => $body_summary,
          'format' => $body_format,
        ],
        'field_opening_date' => $tender_data['opening_date'] ? ['value' => $tender_data['opening_date']] : [],
        'field_closing_date' => $tender_data['closing_date'] ? ['value' => $tender_data['closing_date']] : [],
        'field_tender_categories' => $tender_data['categories'],
        'field_region' => $tender_data['region'] ? ['target_id' => $tender_data['region']] : [],
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

      $this->logger->info('Created tender node @nid from @count images', [
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
   * Parse OCR text to extract tender information.
   *
   * This is a basic implementation that should be enhanced with more
   * sophisticated text analysis, potentially using NLP or pattern matching.
   *
   * @param string $text
   *   The OCR text to parse.
   *
   * @return array
   *   Array with keys: title, opening_date, closing_date, categories, region.
   */
  protected function parseTenderData(string $text): array {
    $data = [
      'title' => $this->extractTitle($text),
      'opening_date' => $this->extractOpeningDate($text),
      'closing_date' => $this->extractClosingDate($text),
      'categories' => [],
      'region' => NULL,
    ];

    return $data;
  }

  /**
   * Extract a title from OCR text.
   *
   * @param string $text
   *   The OCR text.
   *
   * @return string
   *   The extracted title.
   */
  protected function extractTitle(string $text): string {
    // Get the first non-empty line as the title, or truncate to reasonable length.
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    
    if (!empty($lines)) {
      $title = reset($lines);
      // Limit title length to 255 characters (Drupal node title limit).
      if (strlen($title) > 255) {
        $title = substr($title, 0, 252) . '...';
      }
      return $title;
    }

    return 'Tender ' . date('Y-m-d H:i:s');
  }

  /**
   * Extract opening date from OCR text.
   *
   * @param string $text
   *   The OCR text.
   *
   * @return string|null
   *   The date in YYYY-MM-DD format, or NULL if not found.
   */
  protected function extractOpeningDate(string $text): ?string {
    // Look for patterns like "Opening: 2025-11-15" or "Open Date: 15/11/2025".
    if (preg_match('/opening.*?(\d{4}-\d{2}-\d{2})/i', $text, $matches)) {
      return $matches[1];
    }
    
    if (preg_match('/open date.*?(\d{2}[\/\-]\d{2}[\/\-]\d{4})/i', $text, $matches)) {
      return $this->convertDateToISO($matches[1]);
    }

    return NULL;
  }

  /**
   * Extract closing date from OCR text.
   *
   * @param string $text
   *   The OCR text.
   *
   * @return string|null
   *   The date in YYYY-MM-DD format, or NULL if not found.
   */
  protected function extractClosingDate(string $text): ?string {
    // Look for patterns like "Closing: 2025-12-15" or "Close Date: 15/12/2025".
    if (preg_match('/closing.*?(\d{4}-\d{2}-\d{2})/i', $text, $matches)) {
      return $matches[1];
    }
    
    if (preg_match('/close date.*?(\d{2}[\/\-]\d{2}[\/\-]\d{4})/i', $text, $matches)) {
      return $this->convertDateToISO($matches[1]);
    }

    return NULL;
  }

  /**
   * Build a required summary for the tender body field.
   */
  protected function buildSummary(string $text): string {
    $normalized = trim(preg_replace('/\s+/', ' ', $text));
    if ($normalized === '') {
      return 'Summary auto-generated from OCR content. Please refine during proofreading.';
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
   * Convert date from DD/MM/YYYY or DD-MM-YYYY to YYYY-MM-DD.
   *
   * @param string $date
   *   The date string to convert.
   *
   * @return string|null
   *   The date in YYYY-MM-DD format, or NULL if invalid.
   */
  protected function convertDateToISO(string $date): ?string {
    $date = str_replace('/', '-', $date);
    $parts = explode('-', $date);
    
    if (count($parts) === 3) {
      // Assume DD-MM-YYYY format.
      return sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);
    }

    return NULL;
  }

}
