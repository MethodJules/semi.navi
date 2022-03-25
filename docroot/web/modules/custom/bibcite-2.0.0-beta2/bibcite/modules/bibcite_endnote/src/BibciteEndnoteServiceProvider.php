<?php

namespace Drupal\bibcite_endnote;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds EndNote as known format.
 */
class BibciteEndnoteServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') ){
      $definition = $container->getDefinition('http_middleware.negotiation');
      if(is_a($definition->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
        $definition->addMethodCall('registerFormat', ['endnote7', ['text/xml', 'application/x-endnote-refer']]);
        $definition->addMethodCall('registerFormat', ['endnote8', ['text/xml', 'application/x-endnote-refer']]);
        $definition->addMethodCall('registerFormat', ['tagged', ['text/plain', 'application/x-endnote-refer']]);
      }
    }
  }

}
