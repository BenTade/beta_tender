<?php

namespace Drupal\Tests\beta_tender\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\beta_tender\Service\OcrService;

/**
 * Tests for the OCR service.
 *
 * @group beta_tender
 */
class OcrServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['beta_tender', 'system'];

  /**
   * The OCR service.
   *
   * @var \Drupal\beta_tender\Service\OcrService
   */
  protected $ocrService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    
    $this->installConfig(['beta_tender']);
    $this->ocrService = $this->container->get('beta_tender.ocr_service');
  }

  /**
   * Test that the OCR service is available.
   */
  public function testOcrServiceExists(): void {
    $this->assertInstanceOf(OcrService::class, $this->ocrService);
  }

  /**
   * Test OCR availability check.
   */
  public function testOcrAvailability(): void {
    // Without any OCR backend configured, should return FALSE.
    $available = $this->ocrService->isAvailable();
    $this->assertFalse($available, 'OCR should not be available without backend configured');
  }

}
