<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Contributor name computed field.
 */
class ContributorName extends FieldItemList {

  use ComputedItemListTrait {
    get as traitGet;
  }

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    /** @var \Drupal\bibcite_entity\Entity\ContributorInterface $contributor */
    $contributor = $this->parent->getValue();

    $arguments = [];
    foreach ($contributor::getNameParts() as $part) {
      $arguments["@{$part}"] = $contributor->get($part)->value;
    }

    // @todo Dependency injection.
    $format = \Drupal::config('bibcite_entity.contributor.settings')->get('full_name_pattern') ?: '@prefix @first_name @last_name @suffix';
    $full_name = strtr($format, $arguments);
    $value = trim(preg_replace('/\s+/', ' ', $full_name));

    $this->list[0] = $this->createItem(0, $value);
  }

  /**
   * Compute values every time.
   */
  protected function ensureComputedValue() {
    $this->computeValue();
  }

  /**
   * {@inheritdoc}
   */
  public function get($index) {
    if ($index !== 0) {
      throw new \InvalidArgumentException("A contributor entity cannot have multiple names at the same time.");
    }
    return $this->traitGet($index);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    parent::setValue($values, $notify);

    // Gather new string value, so we do not worry how it was set: as string
    // or as array.
    // We cannot use $this->value because it'll be newly calculated value but
    // from old name parts i.e. old value in result.
    // If the parent should be notified about the change,
    // update the contributor entity.
    if ($notify) {
      $value = isset($this->list[0]) ? $this->list[0]->value : NULL;
      $this->updateContributorEntity($value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function appendItem($value = NULL) {
    // Without this, the name field cannot be set via REST, it fails with
    // "Unprocessable Entity: validation failed. name: Name: this field cannot
    // hold more than 1 values." error.
    // There was similar issue with content moderation state computed field in
    // Drupal core, though the way it was fixed doesn't seem to be applicable
    // in our case.
    // We also do not care about handling offset other than 0 here, because it's
    // a single-value field.
    /* @see https://www.drupal.org/node/2943899 */
    /* @see \Drupal\serialization\Normalizer\FieldNormalizer::denormalize */
    /* @see \Drupal\Core\TypedData\ComputedItemListTrait::appendItem */
    /* @see \Drupal\content_moderation\Plugin\Field\ModerationStateFieldItemList */
    $item = $this->createItem(0, $value);
    $this->list[0] = $item;
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($delta) {
    /* @see \Drupal\content_moderation\Plugin\Field\ModerationStateFieldItemList::onChange */
    $this->updateContributorEntity($this->list[$delta]->value);

    parent::onChange($delta);
  }

  /**
   * Updates name parts of the bibcite_contributor entity.
   *
   * @param string $name
   *   Full name string.
   */
  protected function updateContributorEntity($name) {
    /** @var \Drupal\bibcite_entity\Entity\Contributor $entity */
    $entity = $this->getEntity();
    if ($name) {
      $name_parts = \Drupal::service('bibcite.human_name_parser')->parse(
        $name
      );

      // Explicitly map name parts. Entity fields names and keys returned by
      // the name parser do not have to be the same.
      $entity->prefix = $name_parts['prefix'];
      $entity->leading_title = $name_parts['leading_title'];
      $entity->first_name = $name_parts['first_name'];
      $entity->middle_name = $name_parts['middle_name'];
      $entity->last_name = $name_parts['last_name'];
      $entity->nick = $name_parts['nick'];
      $entity->suffix = $name_parts['suffix'];
    }
    else {
      $part_value = ($name === '') ? '' : NULL;
      foreach ($entity::getNameParts() as $name_part) {
        $entity->set($name_part, $part_value);
      }
    }
  }

}
