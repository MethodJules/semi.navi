<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Theme\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Reference entity view builder.
 */
class ReferenceViewBuilder extends EntityViewBuilder {

  /**
   * Serializer service.
   *
   * @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface
   */
  protected $serializer;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, NormalizerInterface $serializer, ConfigFactoryInterface $config_factory, EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager, Registry $theme_registry = NULL, EntityDisplayRepositoryInterface $entity_display_repository = NULL) {
    parent::__construct($entity_type, $entity_repository, $language_manager, $theme_registry, $entity_display_repository);

    $this->serializer = $serializer;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface $serializer */
    $serializer = $container->get('serializer');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = $container->get('entity.repository');
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');
    /** @var \Drupal\Core\Theme\Registry $theme_registry */
    $theme_registry = $container->get('theme.registry');
    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository */
    $entity_display_repository = $container->get('entity_display.repository');
    return new static(
      $entity_type,
      $serializer,
      $config_factory,
      $entity_repository,
      $language_manager,
      $theme_registry,
      $entity_display_repository
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      if ($display->getComponent('citation')) {
        $build[$id]['citation'] = [
          '#theme' => 'bibcite_citation',
          '#data' => $this->serializer->normalize($entity, 'csl'),
          '#entity' => $entity,
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['config:bibcite_entity.reference.settings', 'config:bibcite.settings']);
  }

}
