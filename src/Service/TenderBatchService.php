<?php

namespace Drupal\beta_tender\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service for batch processing of tender creation.
 */
class TenderBatchService {

  use StringTranslationTrait;

  /**
   * The tender creation service.
   *
   * @var \Drupal\beta_tender\Service\TenderCreationService
   */
  protected $tenderCreation;

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
   * Constructs a TenderBatchService object.
   *
   * @param \Drupal\beta_tender\Service\TenderCreationService $tender_creation
   *   The tender creation service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    TenderCreationService $tender_creation,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->tenderCreation = $tender_creation;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('beta_tender');
  }

  /**
   * Process a batch of tender groups.
   *
   * @param array $groups
   *   Array of groups, each containing media IDs.
   *
   * @return array
   *   Batch array for Drupal Batch API.
   */
  public function createBatch(array $groups): array {
    $operations = [];

    foreach ($groups as $group) {
      $operations[] = [
        [$this, 'processTenderGroup'],
        [$group],
      ];
    }

    return [
      'title' => $this->t('Processing Tender Images'),
      'operations' => $operations,
      'finished' => [$this, 'batchFinished'],
      'progress_message' => $this->t('Processed @current out of @total groups.'),
      'error_message' => $this->t('The batch processing encountered an error.'),
    ];
  }

  /**
   * Batch operation callback to process a tender group.
   *
   * @param array $media_ids
   *   Array of media entity IDs.
   * @param array $context
   *   Batch context array.
   */
  public function processTenderGroup(array $media_ids, array &$context): void {
    if (!isset($context['results']['processed'])) {
      $context['results']['processed'] = 0;
      $context['results']['created'] = 0;
      $context['results']['failed'] = 0;
    }

    try {
      // Load media entities.
      $media_storage = $this->entityTypeManager->getStorage('media');
      $media_entities = $media_storage->loadMultiple($media_ids);

      if (empty($media_entities)) {
        $context['results']['failed']++;
        $context['message'] = $this->t('No media entities found for IDs: @ids', [
          '@ids' => implode(', ', $media_ids),
        ]);
        return;
      }

      // Create tender from the group of images.
      $tender = $this->tenderCreation->createTenderFromImages($media_entities);

      if ($tender) {
        $context['results']['created']++;
        $context['results']['processed']++;
        $context['message'] = $this->t('Created tender: @title', [
          '@title' => $tender->label(),
        ]);
      }
      else {
        $context['results']['failed']++;
        $context['message'] = $this->t('Failed to create tender from @count images', [
          '@count' => count($media_entities),
        ]);
      }
    }
    catch (\Exception $e) {
      $context['results']['failed']++;
      $context['message'] = $this->t('Error processing group: @message', [
        '@message' => $e->getMessage(),
      ]);
      $this->logger->error('Batch processing error: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Whether the batch completed successfully.
   * @param array $results
   *   Results from the batch operations.
   * @param array $operations
   *   The remaining operations.
   */
  public function batchFinished(bool $success, array $results, array $operations): void {
    if ($success) {
      $message = $this->t('Successfully created @created tender(s). Failed: @failed', [
        '@created' => $results['created'] ?? 0,
        '@failed' => $results['failed'] ?? 0,
      ]);
      \Drupal::messenger()->addStatus($message);
      
      $this->logger->info('Batch processing completed. Created: @created, Failed: @failed', [
        '@created' => $results['created'] ?? 0,
        '@failed' => $results['failed'] ?? 0,
      ]);
    }
    else {
      $message = $this->t('Batch processing encountered an error.');
      \Drupal::messenger()->addError($message);
      $this->logger->error('Batch processing failed');
    }
  }

}
