<?php

namespace Drupal\beta_tender\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for the Backup and Restore page.
 */
class BackupRestoreController extends ControllerBase {

  /**
   * Displays the Backup and Restore page.
   *
   * @return array
   *   A render array.
   */
  public function content() {
    return [
      '#markup' => $this->t('Backup and Restore functionality will be implemented here.'),
    ];
  }

}
