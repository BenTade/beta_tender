<?php

namespace Drupal\beta_tender\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\NodeInterface;
use Drupal\media\MediaInterface;
use Drupal\file\FileInterface;

/**
 * Service for performing OCR on tender source media images.
 */
class TenderOcrService {

  /**
   * The separator used between OCR text from different files.
   */
  const OCR_SEPARATOR = "\n\n---END OF PAGE---\n\n";

  /**
   * Default OCR languages (Amharic and English).
   */
  const DEFAULT_LANGUAGES = 'amh+eng';

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
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The OCR image service from the ocr_image module.
   *
   * This service provides the getText() method for extracting text from images.
   *
   * @var object
   */
  protected $ocrImageService;

  /**
   * Constructs a TenderOcrService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param object $ocr_image_service
   *   The OCR image service from the ocr_image module.
   *   Must implement getText(string $file_path, string $language, int $limit).
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    FileSystemInterface $file_system,
    $ocr_image_service
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('beta_tender');
    $this->fileSystem = $file_system;
    $this->ocrImageService = $ocr_image_service;
  }

  /**
   * Perform OCR on a tender node's source media images.
   *
   * This method processes all images in the field_source_media field,
   * extracts text using the ocr_image module, and concatenates the results
   * into the field_body field with separators between each image's text.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The tender node to process.
   *
   * @return bool
   *   TRUE if OCR was performed successfully, FALSE otherwise.
   */
  public function processOcrForTender(NodeInterface $node): bool {
    // Validate this is a tender node.
    if ($node->bundle() !== 'tender') {
      $this->logger->error('Cannot perform OCR: node @nid is not a tender.', [
        '@nid' => $node->id(),
      ]);
      return FALSE;
    }

    // Check if the node has source media.
    if (!$node->hasField('field_source_media') || $node->get('field_source_media')->isEmpty()) {
      $this->logger->warning('Tender @nid has no source media for OCR processing.', [
        '@nid' => $node->id(),
      ]);
      return FALSE;
    }

    // Check if ocr_image service is available.
    if ($this->ocrImageService === NULL) {
      $this->logger->error('OCR Image service is not available. Please install and enable the ocr_image module.');
      return FALSE;
    }

    try {
      $ocr_texts = [];
      $media_items = $node->get('field_source_media')->referencedEntities();

      foreach ($media_items as $media) {
        if (!$media instanceof MediaInterface) {
          continue;
        }

        $text = $this->extractTextFromMedia($media);
        if ($text !== NULL) {
          $ocr_texts[] = $text;
        }
      }

      if (empty($ocr_texts)) {
        $this->logger->warning('No OCR text extracted from media for tender @nid.', [
          '@nid' => $node->id(),
        ]);
        return FALSE;
      }

      // Combine all OCR texts with separators.
      $combined_text = implode(self::OCR_SEPARATOR, $ocr_texts);

      // Update the tender body field.
      $this->updateTenderBody($node, $combined_text);

      $this->logger->info('OCR completed for tender @nid. Processed @count image(s).', [
        '@nid' => $node->id(),
        '@count' => count($ocr_texts),
      ]);

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('OCR processing failed for tender @nid: @message', [
        '@nid' => $node->id(),
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Extract text from a media entity using OCR.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to process.
   *
   * @return string|null
   *   The extracted text, or NULL if extraction failed.
   */
  protected function extractTextFromMedia(MediaInterface $media): ?string {
    // Get the file from the media entity.
    $file = $this->getFileFromMedia($media);
    if (!$file instanceof FileInterface) {
      $this->logger->warning('Could not get file from media @mid.', [
        '@mid' => $media->id(),
      ]);
      return NULL;
    }

    // Get the real path of the file.
    $uri = $file->getFileUri();
    $file_path = $this->fileSystem->realpath($uri);

    if (!$file_path || !file_exists($file_path)) {
      $this->logger->warning('File path not found for media @mid: @uri', [
        '@mid' => $media->id(),
        '@uri' => $uri,
      ]);
      return NULL;
    }

    // Check if the file is an image.
    $mime_type = $file->getMimeType();
    if (strpos($mime_type, 'image/') !== 0) {
      $this->logger->info('Skipping non-image file for OCR: @name (type: @type)', [
        '@name' => $file->getFilename(),
        '@type' => $mime_type,
      ]);
      return NULL;
    }

    // Use the ocr_image service to extract text.
    // Language: amh+eng (Amharic + English)
    // Limit: 0 (no word limit).
    $result = $this->ocrImageService->getText($file_path, self::DEFAULT_LANGUAGES, 0);

    if (isset($result['full_text']) && !empty(trim($result['full_text']))) {
      return trim($result['full_text']);
    }

    $this->logger->info('No text extracted from image: @name', [
      '@name' => $file->getFilename(),
    ]);

    return NULL;
  }

  /**
   * Get the file entity from a media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   *
   * @return \Drupal\file\FileInterface|null
   *   The file entity, or NULL if not found.
   */
  protected function getFileFromMedia(MediaInterface $media): ?FileInterface {
    $bundle = $media->bundle();

    // Check for image media type.
    if ($bundle === 'image' && $media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
      return $media->get('field_media_image')->entity;
    }

    // Check for document media type (might be an image file).
    if ($bundle === 'document' && $media->hasField('field_media_document') && !$media->get('field_media_document')->isEmpty()) {
      return $media->get('field_media_document')->entity;
    }

    // Try to get the source field.
    $source = $media->getSource();
    if ($source) {
      $source_field = $source->getSourceFieldDefinition($media->bundle->entity);
      if ($source_field) {
        $field_name = $source_field->getName();
        if ($media->hasField($field_name) && !$media->get($field_name)->isEmpty()) {
          $file = $media->get($field_name)->entity;
          if ($file instanceof FileInterface) {
            return $file;
          }
        }
      }
    }

    return NULL;
  }

  /**
   * Update the tender body field with OCR text.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The tender node.
   * @param string $text
   *   The combined OCR text.
   */
  protected function updateTenderBody(NodeInterface $node, string $text): void {
    if (!$node->hasField('field_body')) {
      return;
    }

    // Resolve the text format to use.
    $format = $this->resolveBodyFormat($node);

    // Generate summary from the text.
    $summary = $this->generateSummary($text);

    $node->set('field_body', [
      'value' => $text,
      'summary' => $summary,
      'format' => $format,
    ]);
    $node->save();
  }

  /**
   * Resolve the text format to use for the body field.
   *
   * Priority:
   * 1. Use existing format from the current field value if present.
   * 2. Try to use 'content' format if it exists.
   * 3. Fall back to 'plain_text'.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The tender node.
   *
   * @return string
   *   The format ID to use.
   */
  protected function resolveBodyFormat(NodeInterface $node): string {
    // First, try to preserve existing format.
    if ($node->hasField('field_body') && !$node->get('field_body')->isEmpty()) {
      $current_format = $node->get('field_body')->format;
      if ($current_format) {
        return $current_format;
      }
    }

    // Try 'content' format if available.
    $format_storage = $this->entityTypeManager->getStorage('filter_format');
    if ($format_storage && $format_storage->load('content')) {
      return 'content';
    }

    // Default to plain_text.
    return 'plain_text';
  }

  /**
   * Generate a summary from the OCR text.
   *
   * @param string $text
   *   The full OCR text.
   *
   * @return string
   *   A summary string (first 600 characters).
   */
  protected function generateSummary(string $text): string {
    // Remove multiple whitespace.
    $normalized = trim(preg_replace('/\s+/', ' ', $text));

    if (empty($normalized)) {
      return 'OCR text extracted from source images.';
    }

    $max_length = 600;
    if (mb_strlen($normalized) <= $max_length) {
      return $normalized;
    }

    return rtrim(mb_substr($normalized, 0, $max_length - 3)) . '...';
  }

}
