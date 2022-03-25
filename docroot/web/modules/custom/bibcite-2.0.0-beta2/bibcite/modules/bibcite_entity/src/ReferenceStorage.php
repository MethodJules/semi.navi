<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines a Controller class for reference items.
 */
class ReferenceStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  protected function saveToDedicatedTables(ContentEntityInterface $entity, $update = TRUE, $names = []) {
    $citekey = NULL;
    /** @var \Drupal\bibcite_entity\Entity\Reference $entity */
    if ($citekey_item = $entity->get('bibcite_citekey')->get(0)) {
      $citekey = $citekey_item->getValue();
    }
    if (!$citekey && $entity->isNew()) {
      $entity->bibcite_citekey = $entity->generateCitekey();
    }
    return parent::saveToDedicatedTables($entity, $update, $names);
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomTableMapping(ContentEntityTypeInterface $entity_type, array $storage_definitions, $prefix = '') {
    $prefix = $prefix ?: ($this->temporary ? 'tmp_' : '');
    return ReferenceDefaultTableMapping::create($entity_type, $storage_definitions, $prefix);
  }

}
