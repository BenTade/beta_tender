<?php

namespace Drupal\beta_tender\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for the proofreading dashboard.
 */
class ProofreadController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a ProofreadController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountInterface $current_user
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Proofreading dashboard page.
   *
   * @return array
   *   Render array for the proofreading dashboard.
   */
  public function proofreadDashboard(): array {
    $build = [
      '#theme' => 'tender_proofread_dashboard',
      '#attached' => [
        'library' => ['beta_tender/proofread'],
      ],
    ];

    // Add "Assign Next Tender" button.
    $build['assign_button'] = [
      '#type' => 'link',
      '#title' => $this->t('Assign Next Tender'),
      '#url' => \Drupal\Core\Url::fromRoute('beta_tender.assign_tender'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
      '#weight' => -10,
    ];

    // Get tenders by status.
    $node_storage = $this->entityTypeManager->getStorage('node');
    
    $statuses = [
      'needs_review' => $this->t('Needs Review'),
      'in_review' => $this->t('In Review'),
      'reviewed' => $this->t('Reviewed'),
    ];

    $tenders_by_status = [];
    
    foreach ($statuses as $status_value => $status_label) {
      $query = $node_storage->getQuery()
        ->condition('type', 'tender')
        ->condition('field_proofreading_status', $status_value)
        ->accessCheck(TRUE)
        ->sort('created', 'ASC');
      
      $tender_ids = $query->execute();
      $tenders = $node_storage->loadMultiple($tender_ids);
      
      $tender_list = [];
      foreach ($tenders as $tender) {
        $source_name = $this->t('N/A');
        if ($tender->hasField('field_tender_source') && !$tender->get('field_tender_source')->isEmpty()) {
          $source = $tender->get('field_tender_source')->entity;
          if ($source) {
            $source_name = $source->getName();
          }
        }
        
        $editor_name = $this->t('Unassigned');
        if ($tender->hasField('field_assigned_editor') && !$tender->get('field_assigned_editor')->isEmpty()) {
          $editor = $tender->get('field_assigned_editor')->entity;
          if ($editor) {
            $editor_name = $editor->getDisplayName();
          }
        }
        
        $tender_list[] = [
          'id' => $tender->id(),
          'title' => $tender->label(),
          'url' => $tender->toUrl('edit-form')->toString(),
          'source' => $source_name,
          'editor' => $editor_name,
          'status' => $status_label,
        ];
      }
      
      $tenders_by_status[$status_value] = [
        'label' => $status_label,
        'tenders' => $tender_list,
        'count' => count($tender_list),
      ];
    }

    $build['#filters'] = $statuses;
    $build['#tenders'] = $tenders_by_status;

    return $build;
  }

  /**
   * Assign the next tender to the current user.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response to the tender edit page.
   */
  public function assignNextTender(): RedirectResponse {
    $node_storage = $this->entityTypeManager->getStorage('node');
    
    // Find the oldest tender with "needs_review" status.
    $query = $node_storage->getQuery()
      ->condition('type', 'tender')
      ->condition('field_proofreading_status', 'needs_review')
      ->accessCheck(TRUE)
      ->sort('created', 'ASC')
      ->range(0, 1);
    
    $tender_ids = $query->execute();
    
    if (empty($tender_ids)) {
      $this->messenger()->addWarning($this->t('No tenders available for review.'));
      return new RedirectResponse(\Drupal\Core\Url::fromRoute('beta_tender.proofread')->toString());
    }

    $tender_id = reset($tender_ids);
    $tender = $node_storage->load($tender_id);
    
    if ($tender) {
      // Assign to current user and change status to "in_review".
      $tender->set('field_assigned_editor', $this->currentUser->id());
      $tender->set('field_proofreading_status', 'in_review');
      $tender->save();
      
      $this->messenger()->addStatus($this->t('Tender "@title" has been assigned to you.', [
        '@title' => $tender->label(),
      ]));
      
      // Redirect to the tender edit page.
      return new RedirectResponse($tender->toUrl('edit-form')->toString());
    }

    $this->messenger()->addError($this->t('Failed to assign tender.'));
    return new RedirectResponse(\Drupal\Core\Url::fromRoute('beta_tender.proofread')->toString());
  }

}
