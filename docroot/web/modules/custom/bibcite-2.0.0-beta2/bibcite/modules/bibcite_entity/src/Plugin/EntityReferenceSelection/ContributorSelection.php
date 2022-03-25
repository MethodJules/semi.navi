<?php

namespace Drupal\bibcite_entity\Plugin\EntityReferenceSelection;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Provides specific access control for the contributor entity type.
 *
 * @EntityReferenceSelection(
 *   id = "default:bibcite_contributor",
 *   label = @Translation("Contributor selection"),
 *   entity_types = {"bibcite_contributor"},
 *   group = "default",
 *   weight = 1
 * )
 */
class ContributorSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function createNewEntity($entity_type_id, $bundle, $label, $uid) {
    $entity = parent::createNewEntity($entity_type_id, $bundle, $label, $uid);
    $entity->name = $label;
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    $group = $query->orConditionGroup();
    foreach (Contributor::getNameParts() as $part) {
      $group->condition($part, $match, $match_operator);
    }

    $query->condition($group);

    return $query;
  }

}
