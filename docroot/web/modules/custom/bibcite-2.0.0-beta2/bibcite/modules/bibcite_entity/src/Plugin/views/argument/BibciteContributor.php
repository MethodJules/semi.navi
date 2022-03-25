<?php

namespace Drupal\bibcite_entity\Plugin\views\argument;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a contributor id.
 *
 * @ViewsArgument("bibcite_contributor")
 */
class BibciteContributor extends IdArgumentBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get('entity_type.manager');
    $storage = $entity_manager->getStorage('bibcite_contributor');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $storage
    );
  }

}
