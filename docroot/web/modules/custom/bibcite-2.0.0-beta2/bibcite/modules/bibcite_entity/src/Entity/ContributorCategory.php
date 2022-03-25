<?php

namespace Drupal\bibcite_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Contributor category entity.
 *
 * @ConfigEntityType(
 *   id = "bibcite_contributor_category",
 *   label = @Translation("Contributor category"),
 *   handlers = {
 *     "list_builder" = "Drupal\bibcite_entity\ContributorCategoryListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bibcite_entity\Form\ContributorCategoryForm",
 *       "edit" = "Drupal\bibcite_entity\Form\ContributorCategoryForm",
 *       "delete" = "Drupal\bibcite_entity\Form\ContributorCategoryDeleteForm"
 *     },
 *   },
 *   config_prefix = "bibcite_contributor_category",
 *   admin_permission = "administer bibcite_reference",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/bibcite/contributor/settings/category/add",
 *     "edit-form" = "/admin/structure/bibcite/contributor/settings/category/{bibcite_contributor_category}",
 *     "delete-form" = "/admin/structure/bibcite/contributor/settings/category/{bibcite_contributor_category}/delete",
 *     "collection" = "/admin/structure/bibcite/contributor/settings/category"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *   }
 * )
 */
class ContributorCategory extends ConfigEntityBase implements ContributorCategoryInterface {

  /**
   * The Contributor category ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Contributor category label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Contributor category weight.
   *
   * @var string
   */
  protected $weight;

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->get('weight');
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    return $this->set('weight', $weight);
  }

}
