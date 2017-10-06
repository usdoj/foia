<?php

namespace Drupal\foia_webform;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class AgencyLookupService.
 */
class AgencyLookupService implements AgencyLookupServiceInterface {

  /**
   * The entity query service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentFromWebform($webformId) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'agency_component')
      ->condition('field_request_submission_form', $webformId);
    $nid = $query->execute();

    return ($nid) ? Node::load(reset($nid)) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAgencyFromComponent(NodeInterface $agencyComponent) {
    $agencyTerm = NULL;
    if (!$agencyComponent->get('field_agency')->isEmpty()) {
      $agencyTerm = $agencyComponent->get('field_agency')->getEntity();
    }
    return $agencyTerm;
  }

}