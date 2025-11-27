<?php

namespace Drupal\beta_tender\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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

  use StringTranslationTrait;

  /**
   * The tender OCR service.
   *
   * @var \Drupal\beta_tender\Service\TenderOcrService
   */
  protected $tenderOcrService;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

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
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    TenderOcrService $tender_ocr_service,
    MessengerInterface $messenger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tenderOcrService = $tender_ocr_service;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('beta_tender.tender_ocr'),
      $container->get('messenger')
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
      $this->messenger->addWarning($this->t('OCR action can only be performed on tender nodes.'));
      return;
    }

    // Perform OCR processing.
    $success = $this->tenderOcrService->processOcrForTender($entity);

    if ($success) {
      $this->messenger->addStatus($this->t('OCR completed successfully for tender: @title', [
        '@title' => $entity->label(),
      ]));
    }
    else {
      $this->messenger->addError($this->t('OCR processing failed for tender: @title', [
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
      return $return_as_object ? AccessResult::forbidden() : FALSE;
    }

    // Only allow on tender nodes.
    if ($object->bundle() !== 'tender') {
      return $return_as_object ? AccessResult::forbidden() : FALSE;
    }

    // Check if user has permission to use OCR action.
    $access = $account->hasPermission('use tender ocr action');

    // Also check if user can update the node.
    $update_access = $object->access('update', $account);

    $result = $access && $update_access;

    if ($return_as_object) {
      return $result
        ? AccessResult::allowed()
        : AccessResult::forbidden();
    }

    return $result;
  }

}
