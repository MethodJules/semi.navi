<?php

namespace Drupal\bibcite_entity\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Provides specific access control for the contributor entity type.
 *
 * @todo This is a temporary solution to avoid a bug with "entity_reference_autocomplete" widget.
 *
 * @EntityReferenceSelection(
 *   id = "default:bibcite_keyword",
 *   label = @Translation("Keyword selection"),
 *   entity_types = {"bibcite_keyword"},
 *   group = "default",
 *   weight = 1
 * )
 */
class KeywordSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $this->configuration['target_bundles'] = NULL;
    return parent::buildEntityQuery($match, $match_operator);
  }

}
