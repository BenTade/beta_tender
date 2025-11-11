<?php

namespace Drupal\beta_tender\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Beta Tender settings.
 */
class BetaTenderSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'beta_tender_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['beta_tender.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('beta_tender.settings');

    $form['ocr_backend'] = [
      '#type' => 'radios',
      '#title' => $this->t('OCR Backend'),
      '#description' => $this->t('Select the OCR module to use for text extraction from images.'),
      '#options' => $this->getAvailableBackends(),
      '#default_value' => $config->get('ocr_backend') ?? '',
      '#required' => TRUE,
    ];

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('The selected OCR backend module must be installed and enabled. If no options are available, please install either the <code>document_ocr</code> or <code>ocr_image</code> module.') . '</p>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('beta_tender.settings')
      ->set('ocr_backend', $form_state->getValue('ocr_backend'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get available OCR backend options.
   *
   * @return array
   *   Array of available backend options.
   */
  protected function getAvailableBackends(): array {
    $options = [];

    if (\Drupal::moduleHandler()->moduleExists('document_ocr')) {
      $options['document_ocr'] = $this->t('Document OCR');
    }

    if (\Drupal::moduleHandler()->moduleExists('ocr_image')) {
      $options['ocr_image'] = $this->t('OCR Image');
    }

    if (empty($options)) {
      $options['none'] = $this->t('No OCR modules available');
    }

    return $options;
  }

}
