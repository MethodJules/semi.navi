<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\bibcite_entity\Entity\ReferenceType;

/**
 * Provides dynamic permissions for References of different types.
 */
class ReferencePermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of Reference type permissions.
   *
   * @return array
   *   The Reference type permissions.
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function referenceTypePermissions() {
    $perms = [];
    // Generate Reference permissions for all types.
    foreach (ReferenceType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of Reference permissions for a given type.
   *
   * @param \Drupal\bibcite_entity\Entity\ReferenceType $type
   *   The reference type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(ReferenceType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      "create $type_id bibcite_reference" => [
        'title' => $this->t('%type_name: Create new Reference entity', $type_params),
      ],
      "edit own $type_id bibcite_reference" => [
        'title' => $this->t('%type_name: Edit own Reference entity', $type_params),
      ],
      "edit any $type_id bibcite_reference" => [
        'title' => $this->t('%type_name: Edit any Reference entity', $type_params),
      ],
      "delete own $type_id bibcite_reference" => [
        'title' => $this->t('%type_name: Delete own Reference entity', $type_params),
      ],
      "delete any $type_id bibcite_reference" => [
        'title' => $this->t('%type_name: Delete any Reference entity', $type_params),
      ],
      "view bibcite_reference $type_id revisions" => [
        'title' => $this->t('%type_name: View revisions', $type_params),
        'description' => t('To view a revision, you also need permission to view the reference item.'),
      ],
      "revert bibcite_reference $type_id revisions" => [
        'title' => $this->t('%type_name: Revert revisions', $type_params),
        'description' => t('To revert a revision, you also need permission to edit the reference item.'),
      ],
      "delete bibcite_reference $type_id revisions" => [
        'title' => $this->t('%type_name: Delete revisions', $type_params),
        'description' => $this->t('To delete a revision, you also need permission to delete the reference item.'),
      ],
    ];
  }

}
