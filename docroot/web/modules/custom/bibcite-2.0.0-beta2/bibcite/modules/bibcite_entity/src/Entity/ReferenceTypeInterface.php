<?php

namespace Drupal\bibcite_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

/**
 * Provides an interface for defining Reference type entities.
 */
interface ReferenceTypeInterface extends ConfigEntityInterface, RevisionableEntityBundleInterface {

  /**
   * Sets whether a new revision should be created by default.
   *
   * @param bool $new_revision
   *   TRUE if a new revision should be created by default.
   */
  public function setNewRevision($new_revision);

  /**
   * Get description.
   *
   * @return string
   *   Short description.
   */
  public function getDescription();

  /**
   * Set description.
   *
   * @param string $desc
   *   String of description.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceTypeInterface
   *   Callable entity object.
   */
  public function setDescription($desc);

  /**
   * Get fields configuration array.
   *
   * @return array
   *   Array of fields configuration.
   */
  public function getFields();

  /**
   * Set fields configuration array.
   *
   * @param array $fields
   *   Array of fields configuration.
   *
   * @return \Drupal\bibcite_entity\Entity\ReferenceTypeInterface
   *   Callable entity object.
   */
  public function setFields(array $fields);

  /**
   * Check if properties should be overridden for this type.
   *
   * @return bool
   *   TRUE if override is required, false otherwise.
   */
  public function isRequiredOverride();

  /**
   * Gets the preview mode.
   *
   * @return int
   *   DRUPAL_DISABLED, DRUPAL_OPTIONAL or DRUPAL_REQUIRED.
   */
  public function getPreviewMode();

  /**
   * Sets the preview mode.
   *
   * @param int $preview_mode
   *   DRUPAL_DISABLED, DRUPAL_OPTIONAL or DRUPAL_REQUIRED.
   */
  public function setPreviewMode($preview_mode);

  /**
   * Gets pattern for citekey generation.
   *
   * @return string
   *   Pattern for citekey generation.
   */
  public function getCitekeyPattern();

  /**
   * Sets pattern for citekey generation.
   *
   * @param string $citekey
   *   String of pattern.
   *
   * @return $this
   */
  public function setCitekeyPattern($citekey);

}
