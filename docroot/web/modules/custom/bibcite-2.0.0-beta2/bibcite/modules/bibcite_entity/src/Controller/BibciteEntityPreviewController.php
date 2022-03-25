<?php

namespace Drupal\bibcite_entity\Controller;

use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a controller to render a single bibcite entity in preview.
 */
class BibciteEntityPreviewController extends EntityViewController {

  /**
   * Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $repository;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, EntityRepositoryInterface $repository) {
    parent::__construct($entity_type_manager, $renderer);
    $this->repository = $repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager */
    $entity_manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $container->get('renderer');
    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $repository */
    $repository = $container->get('entity.repository');
    return new static($entity_manager, $renderer, $repository);
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $bibcite_reference_preview, $view_mode_id = 'default', $langcode = NULL) {
    $bibcite_reference_preview->preview_view_mode = $view_mode_id;
    $build = parent::view($bibcite_reference_preview, $view_mode_id);

    $build['#attached']['library'][] = 'bibcite_entity/reference.preview';

    // Don't render cache previews.
    unset($build['#cache']);

    return $build;
  }

  /**
   * The _title_callback for the page that renders a reference in preview.
   *
   * @param \Drupal\Core\Entity\EntityInterface $bibcite_reference_preview
   *   The current reference entity.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $bibcite_reference_preview) {
    return $this->repository->getTranslationFromContext($bibcite_reference_preview)->label();
  }

}
