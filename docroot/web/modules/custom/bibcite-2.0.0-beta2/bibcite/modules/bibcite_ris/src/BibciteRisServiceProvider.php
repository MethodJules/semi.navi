<?php

namespace Drupal\bibcite_ris;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds RIS as known format.
 */
class BibciteRisServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') ) {
      $definition = $container->getDefinition('http_middleware.negotiation');
      if (is_a($definition->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
        $definition->addMethodCall('registerFormat', ['ris', ['text/plain', 'application/x-research-info-systems']]);
      }
    }
  }

}
