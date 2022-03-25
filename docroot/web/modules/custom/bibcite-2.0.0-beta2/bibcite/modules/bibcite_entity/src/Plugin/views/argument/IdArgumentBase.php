<?php

namespace Drupal\bibcite_entity\Plugin\views\argument;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\views\Plugin\views\argument\NumericArgument;

/**
 * Class IdArgumentBase.
 */
abstract class IdArgumentBase extends NumericArgument {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs the IdArgumentBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
  }

  /**
   * Override the behavior of titleQuery(). Get the title of the entity.
   */
  public function titleQuery() {
    $titles = [];

    $entities = $this->storage->loadMultiple($this->value);
    foreach ($entities as $entity) {
      $titles[] = $entity->label();
    }
    return $titles;
  }

}
