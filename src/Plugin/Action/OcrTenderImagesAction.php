<?php

namespace Drupal\beta_tender\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\beta_tender\Service\TenderOcrService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an action to perform OCR on tender source media images.
 *
 * This action extracts text from all images in the source_media field
 * of a tender node using OCR (Optical Character Recognition) and
 * populates the tender_body field with the results.
 *
 * The OCR uses Amharic and English languages with no word limit.
 * Results are separated by a marker to distinguish text from different images.
 *
 * @Action(
 *   id = "beta_tender_ocr_images",
 *   label = @Translation("OCR tender source images"),
 *   type = "node",
 *   category = @Translation("Tender"),
 *   confirm = TRUE,
 * )
 */
class OcrTenderImagesAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The tender OCR service.
   *
   * @var \Drupal\beta_tender\Service\TenderOcrService
   */
  protected $tenderOcrService;

  /**
   * Constructs an OcrTenderImagesAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\beta_tender\Service\TenderOcrService $tender_ocr_service
   *   The tender OCR service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TenderOcrService $tender_ocr_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tenderOcrService = $tender_ocr_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('beta_tender.tender_ocr')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity instanceof NodeInterface) {
      return;
    }

    // Check if this is a tender node.
    if ($entity->bundle() !== 'tender') {
      \Drupal::messenger()->addWarning(t('OCR action can only be performed on tender nodes.'));
      return;
    }

    // Perform OCR processing.
    $success = $this->tenderOcrService->processOcrForTender($entity);

    if ($success) {
      \Drupal::messenger()->addStatus(t('OCR completed successfully for tender: @title', [
        '@title' => $entity->label(),
      ]));
    }
    else {
      \Drupal::messenger()->addError(t('OCR processing failed for tender: @title', [
        '@title' => $entity->label(),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    if (!$object instanceof NodeInterface) {
      return $return_as_object ? \Drupal\Core\Access\AccessResult::forbidden() : FALSE;
    }

    // Only allow on tender nodes.
    if ($object->bundle() !== 'tender') {
      return $return_as_object ? \Drupal\Core\Access\AccessResult::forbidden() : FALSE;
    }

    // Check if user has permission to use OCR action.
    $access = $account->hasPermission('use tender ocr action');

    // Also check if user can update the node.
    $update_access = $object->access('update', $account);

    $result = $access && $update_access;

    if ($return_as_object) {
      return $result
        ? \Drupal\Core\Access\AccessResult::allowed()
        : \Drupal\Core\Access\AccessResult::forbidden();
    }

    return $result;
  }

}
