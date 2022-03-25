<?php

namespace Drupal\bibcite_entity;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the Keywords entity type.
 */
class KeywordViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['bibcite_keyword']['id']['argument'] = [
      'id' => 'bibcite_keyword',
      'name field' => 'name',
      'numeric' => TRUE,
    ];

    // @todo Use $this->entityTypeManager only, once Drupal 8.9.0 is released.
    $entity_manager = isset($this->entityTypeManager) ? $this->entityTypeManager : $this->entityManager;
    $entity_type = $entity_manager->getDefinition('bibcite_reference');

    $data[$this->entityType->getBaseTable()] += [
      'reverse__' . $entity_type->id() . '__' . $this->entityType->id() => [
        'relationship' => [
          'title' => $this->t('Reference using keywords'),
          'label' => $entity_type->getLabel(),
          'group' => $this->entityType->getLabel(),
          'id' => 'entity_reverse',
          'base' => $entity_type->getDataTable() ?: $entity_type->getBaseTable(),
          'entity_type' => $entity_type->id(),
          'base field' => $entity_type->getKey('id'),
          'field_name' => 'keywords',
          'field table' => 'bibcite_reference__keywords',
          'field field' => 'keywords_target_id',
          'join_extra' => [
            [
              'field' => 'deleted',
              'value' => 0,
              'numeric' => TRUE,
            ],
          ],
        ],
      ],
    ];

    return $data;
  }

}
