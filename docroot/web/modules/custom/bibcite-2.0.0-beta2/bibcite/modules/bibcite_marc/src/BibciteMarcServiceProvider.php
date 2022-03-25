<?php

namespace Drupal\bibcite_marc;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds MARC as known format.
 */
class BibciteMarcServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') ) {
      $definition = $container->getDefinition('http_middleware.negotiation');
      if (is_a($definition->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
        $definition->addMethodCall('registerFormat', ['marc', ['application/marc']]);
      }
    }
  }

}
