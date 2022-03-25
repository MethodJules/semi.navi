<?php

namespace Drupal\bibcite_entity\Normalizer;

use Drupal\serialization\Normalizer\EntityNormalizer;

/**
 * Base normalizer class for bibcite formats.
 */
class KeywordNormalizer extends EntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var array
   */
  protected $supportedInterfaceOrClass = ['Drupal\bibcite_entity\Entity\Keyword'];

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    $entity = parent::denormalize($data, $class, $format, $context);
    $entity_manager = $this->getEntityTypeManager();

    if (!empty($context['keyword_deduplication'])) {
      $storage = $entity_manager->getStorage('bibcite_keyword');
      $label_key = $storage->getEntityType()->getKey('label');
      $query = $storage->getQuery()
        ->accessCheck()
        ->condition($label_key, trim($entity->label()))
        ->range(0, 1);

      $ids = $query->execute();
      if ($ids && ($result = $storage->loadMultiple($ids))) {
        return reset($result);
      }
    }
    return $entity;
  }

}
