<?php

namespace Drupal\bibcite_entity\Controller;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReferenceViewController.
 *
 * @package Drupal\bibcite_entity\Controller
 */
class ReferenceViewController extends EntityViewController {

  /**
   * Reference config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, ImmutableConfig $config) {
    parent::__construct($entity_type_manager, $renderer);
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $container->get('renderer');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    $config = $config_factory->get('bibcite_entity.reference.settings');
    return new static(
      $entity_manager,
      $renderer,
      $config
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $bibcite_reference, $view_mode = 'full') {
    $view_mode = $this->config->get('display_override.reference_page_view_mode');
    return parent::view($bibcite_reference, $view_mode);
  }

  /**
   * {@inheritdoc}
   */
  public function viewRevision(EntityInterface $_entity_revision, $view_mode = 'full') {
    $view_mode = $this->config->get('display_override.reference_page_view_mode');
    return parent::viewRevision($_entity_revision, $view_mode);
  }

  /**
   * Page title callback for a reference revision.
   *
   * @param \Drupal\Core\Entity\EntityInterface $bibcite_reference_revision
   *   The reference revision.
   *
   * @return string
   *   The page title.
   */
  public function revisionTitle(EntityInterface $bibcite_reference_revision = NULL) {
    return $bibcite_reference_revision->label();
  }

}
