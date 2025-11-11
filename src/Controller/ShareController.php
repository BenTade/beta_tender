<?php

namespace Drupal\beta_tender\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for the share tenders page.
 */
class ShareController extends ControllerBase {

  /**
   * Share dashboard page.
   *
   * Redirects to Entity Share if available, otherwise displays info.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Render array or redirect response.
   */
  public function shareDashboard() {
    // Check if Entity Share module is enabled.
    if (\Drupal::moduleHandler()->moduleExists('entity_share')) {
      // Try to redirect to Entity Share admin page.
      // The exact route depends on the Entity Share module version.
      // Common routes: entity_share.admin_channels, entity_share_client.admin_content
      
      if (\Drupal::service('router.route_provider')->getRoutesByNames(['entity_share_client.admin_content'])) {
        $url = Url::fromRoute('entity_share_client.admin_content');
        return new RedirectResponse($url->toString());
      }
      
      if (\Drupal::service('router.route_provider')->getRoutesByNames(['entity_share.admin_channels'])) {
        $url = Url::fromRoute('entity_share.admin_channels');
        return new RedirectResponse($url->toString());
      }
    }

    // If Entity Share is not available, display information.
    $build = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--warning">' . 
        $this->t('The Entity Share module is not installed or enabled. This module is required for content synchronization features.') .
        '</div>',
    ];

    $build['info'] = [
      '#type' => 'markup',
      '#markup' => '<div class="tender-share-info">' .
        '<h2>' . $this->t('Content Synchronization') . '</h2>' .
        '<p>' . $this->t('The Share Tenders feature allows you to synchronize reviewed tender content to production websites using the Entity Share module.') . '</p>' .
        '<h3>' . $this->t('Installation Instructions') . '</h3>' .
        '<ol>' .
        '<li>' . $this->t('Install and enable the Entity Share module: <code>composer require drupal/entity_share</code>') . '</li>' .
        '<li>' . $this->t('Enable the module: <code>drush en entity_share entity_share_client</code>') . '</li>' .
        '<li>' . $this->t('Configure Entity Share channels for the <em>tender</em> content type') . '</li>' .
        '<li>' . $this->t('Return to this page to access the synchronization interface') . '</li>' .
        '</ol>' .
        '<h3>' . $this->t('Workflow') . '</h3>' .
        '<p>' . $this->t('Once configured, you will be able to:') . '</p>' .
        '<ul>' .
        '<li>' . $this->t('Select tenders with "Reviewed" status') . '</li>' .
        '<li>' . $this->t('Push content to production sites') . '</li>' .
        '<li>' . $this->t('Track synchronization status') . '</li>' .
        '</ul>' .
        '</div>',
    ];

    return $build;
  }

}
