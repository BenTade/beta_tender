<?php

namespace Drupal\beta_tender\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\beta_tender\Service\TenderBatchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for processing tender image batches.
 */
class ProcessTenderBatchForm extends FormBase {

  /**
   * The tender batch service.
   *
   * @var \Drupal\beta_tender\Service\TenderBatchService
   */
  protected $batchService;

  /**
   * Constructs a ProcessTenderBatchForm object.
   *
   * @param \Drupal\beta_tender\Service\TenderBatchService $batch_service
   *   The tender batch service.
   */
  public function __construct(TenderBatchService $batch_service) {
    $this->batchService = $batch_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('beta_tender.batch_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'beta_tender_process_batch';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // This form is typically embedded in the image arrangement page.
    // The actual processing is triggered from there.
    
    $form['info'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This form processes selected tender images using OCR and creates tender nodes.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process OCR and Create Tenders'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the selected groups from form state or request.
    $groups = $form_state->getValue('selected_groups', []);
    
    if (empty($groups)) {
      $this->messenger()->addWarning($this->t('No tender groups selected for processing.'));
      return;
    }

    // Create and set the batch.
    $batch = $this->batchService->createBatch($groups);
    batch_set($batch);
  }

}
