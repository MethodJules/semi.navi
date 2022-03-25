<?php

namespace Drupal\bibcite_entity\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Alters existing routes for a specific collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.bibcite_reference.revision')) {
      $route->setDefault('_controller', '\Drupal\bibcite_entity\Controller\ReferenceViewController::viewRevision');
      $route->setDefault('_title_callback', '\Drupal\bibcite_entity\Controller\ReferenceViewController::revisionTitle');
    }
    if ($route = $collection->get('entity.bibcite_reference.canonical')) {
      $config = $this->configFactory->get('bibcite_entity.reference.settings');
      $view_mode = $config->get('display_override.reference_page_view_mode');
      // Let's leave it for now for compatibility with default controller.
      // @todo Remove it (and 3 previous lines)?
      $route->setDefault('_entity_view', 'bibcite_reference.' . $view_mode);
      // View mode is cached on the route level when passed as route param.
      // Clearing routes' cache is required in this case to apply
      // "Reference page view mode" setting changes.
      // So, let's move Reference page view mode logic to custom controller
      // instead.
      $route->setDefault('_controller', '\Drupal\bibcite_entity\Controller\ReferenceViewController::view');
    }
    if ($route = $collection->get('entity.bibcite_reference.version_history')) {
      $route->setOption('_admin_route', TRUE);
    }
    if ($route = $collection->get('entity.bibcite_reference.revision_revert_form')) {
      $route->setOption('_admin_route', TRUE);
    }
    if ($route = $collection->get('bibcite_reference.revision_delete_confirm')) {
      $route->setOption('_admin_route', TRUE);
    }
  }

}
