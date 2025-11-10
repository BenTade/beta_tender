<?php

namespace Drupal\beta_tender\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;

/**
 * Service for OCR processing of images.
 *
 * Acts as an adapter that interfaces with either document_ocr or ocr_image
 * modules based on the configured backend.
 */
class OcrService {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs an OcrService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('beta_tender');
  }

  /**
   * Extract text from an image file using OCR.
   *
   * @param \Drupal\file\FileInterface $file
   *   The image file to process.
   *
   * @return string
   *   The extracted text, or empty string if OCR fails.
   */
  public function extractText(FileInterface $file): string {
    $config = $this->configFactory->get('beta_tender.settings');
    $backend = $config->get('ocr_backend');

    if (empty($backend)) {
      $this->logger->error('No OCR backend configured. Please configure at /admin/config/beta_tender');
      return '';
    }

    try {
      switch ($backend) {
        case 'document_ocr':
          return $this->extractWithDocumentOcr($file);

        case 'ocr_image':
          return $this->extractWithOcrImage($file);

        default:
          $this->logger->error('Unknown OCR backend: @backend', ['@backend' => $backend]);
          return '';
      }
    }
    catch (\Exception $e) {
      $this->logger->error('OCR processing failed: @message', ['@message' => $e->getMessage()]);
      return '';
    }
  }

  /**
   * Extract text using document_ocr module.
   *
   * @param \Drupal\file\FileInterface $file
   *   The image file to process.
   *
   * @return string
   *   The extracted text.
   */
  protected function extractWithDocumentOcr(FileInterface $file): string {
    if (!\Drupal::moduleHandler()->moduleExists('document_ocr')) {
      throw new \Exception('document_ocr module is not enabled');
    }

    // Check if document_ocr provides a service or function to extract text.
    // This is a placeholder implementation that should be adapted based on
    // the actual document_ocr module API.
    $file_uri = $file->getFileUri();
    $file_path = \Drupal::service('file_system')->realpath($file_uri);

    if (!$file_path || !file_exists($file_path)) {
      throw new \Exception('File not found: ' . $file_uri);
    }

    // Example: Call document_ocr API (adjust based on actual API).
    // This is a mock implementation.
    $text = "OCR text extracted from: " . $file->getFilename();
    
    $this->logger->info('Processed file @filename with document_ocr', [
      '@filename' => $file->getFilename(),
    ]);

    return $text;
  }

  /**
   * Extract text using ocr_image module.
   *
   * @param \Drupal\file\FileInterface $file
   *   The image file to process.
   *
   * @return string
   *   The extracted text.
   */
  protected function extractWithOcrImage(FileInterface $file): string {
    if (!\Drupal::moduleHandler()->moduleExists('ocr_image')) {
      throw new \Exception('ocr_image module is not enabled');
    }

    // Check if ocr_image provides a service or function to extract text.
    // This is a placeholder implementation that should be adapted based on
    // the actual ocr_image module API.
    $file_uri = $file->getFileUri();
    $file_path = \Drupal::service('file_system')->realpath($file_uri);

    if (!$file_path || !file_exists($file_path)) {
      throw new \Exception('File not found: ' . $file_uri);
    }

    // Example: Call ocr_image API (adjust based on actual API).
    // This is a mock implementation.
    $text = "OCR text extracted from: " . $file->getFilename();
    
    $this->logger->info('Processed file @filename with ocr_image', [
      '@filename' => $file->getFilename(),
    ]);

    return $text;
  }

  /**
   * Check if OCR backend is configured and available.
   *
   * @return bool
   *   TRUE if OCR is available, FALSE otherwise.
   */
  public function isAvailable(): bool {
    $config = $this->configFactory->get('beta_tender.settings');
    $backend = $config->get('ocr_backend');

    if (empty($backend)) {
      return FALSE;
    }

    return \Drupal::moduleHandler()->moduleExists($backend);
  }

}
