<?php

namespace Drupal\bibcite_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Reference type entity.
 *
 * @ConfigEntityType(
 *   id = "bibcite_reference_type",
 *   label = @Translation("Reference type"),
 *   handlers = {
 *     "access" = "Drupal\bibcite_entity\ReferenceTypeAccessControlHandler",
 *     "list_builder" = "Drupal\bibcite_entity\ReferenceTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\bibcite_entity\Form\ReferenceTypeForm",
 *       "edit" = "Drupal\bibcite_entity\Form\ReferenceTypeForm",
 *       "delete" = "Drupal\bibcite_entity\Form\ReferenceTypeDeleteForm"
 *     },
 *   },
 *   config_prefix = "bibcite_reference_type",
 *   bundle_of = "bibcite_reference",
 *   admin_permission = "administer bibcite_reference",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/bibcite/reference/settings/types/add",
 *     "edit-form" = "/admin/structure/bibcite/reference/settings/types/{bibcite_reference_type}",
 *     "delete-form" = "/admin/structure/bibcite/reference/settings/types/{bibcite_reference_type}/delete",
 *     "collection" = "/admin/structure/bibcite/reference/settings/types"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "new_revision",
 *     "override",
 *     "preview_mode",
 *     "citekey_pattern",
 *     "fields",
 *   }
 * )
 */
class ReferenceType extends ConfigEntityBundleBase implements ReferenceTypeInterface {

  /**
   * The Reference type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Reference type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Reference type description.
   *
   * @var string
   */
  protected $description;

  /**
   * Default value of the 'Create new revision' checkbox of this reference type.
   *
   * @var bool
   */
  protected $new_revision = FALSE;

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    $this->new_revision = $new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($desc) {
    $this->description = $desc;
    return $this;
  }

  /**
   * The Reference type override flag.
   *
   * @var bool
   */
  protected $override = FALSE;

  /**
   * The preview mode.
   *
   * @var int
   */
  protected $preview_mode = DRUPAL_OPTIONAL;

  /**
   * Pattern for citekey generation.
   *
   * @var string
   */
  protected $citekey_pattern;

  /**
   * The Reference fields configuration.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * {@inheritdoc}
   */
  public function setFields(array $fields) {
    $this->fields = $fields;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequiredOverride() {
    return $this->override;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewMode() {
    return $this->preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function setPreviewMode($preview_mode) {
    $this->preview_mode = $preview_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function getCitekeyPattern() {
    return $this->citekey_pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function setCitekeyPattern($citekey) {
    $this->citekey_pattern = $citekey;
    return $this;
  }

}
