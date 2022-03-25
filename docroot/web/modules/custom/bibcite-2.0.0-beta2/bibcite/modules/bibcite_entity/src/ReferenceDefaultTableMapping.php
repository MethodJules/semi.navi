<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\Entity\Sql\DefaultTableMapping;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Class ReferenceDefaultTableMapping.
 *
 * @package Drupal\bibcite_entity
 */
class ReferenceDefaultTableMapping extends DefaultTableMapping {

  /**
   * {@inheritdoc}
   */
  public function requiresDedicatedTableStorage(FieldStorageDefinitionInterface $storage_definition) {
    if ($storage_definition->getName() === 'bibcite_citekey') {
      return TRUE;
    }
    return parent::requiresDedicatedTableStorage($storage_definition);
  }

}
