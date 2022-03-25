<?php

namespace Drupal\bibcite_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Reference entity.
 *
 * @see \Drupal\bibcite_entity\Entity\Reference.
 */
class ReferenceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $type = $entity->bundle();
    /** @var \Drupal\bibcite_entity\Entity\Reference $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIf($entity->isPublished() && $account->hasPermission('view bibcite_reference'))
          ->orIf(AccessResult::allowedIf(!$entity->isPublished() && $account->hasPermission('view own unpublished bibcite_reference') && $account->isAuthenticated() && $entity->getOwnerId() == $account->id()))
          ->cachePerPermissions()->cachePerUser()->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit any bibcite_reference')
          ->orIf(AccessResult::allowedIfHasPermission($account, "edit any $type bibcite_reference"))
          ->orIf(AccessResult::allowedIf($entity->getOwnerId() == $account->id()
            && ($account->hasPermission('edit own bibcite_reference')
              || $account->hasPermission("edit own $type bibcite_reference")))
            ->cachePerPermissions()->cachePerUser());

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete any bibcite_reference')
          ->orIf(AccessResult::allowedIfHasPermission($account, "delete any $type bibcite_reference"))
          ->orIf(AccessResult::allowedIf($entity->getOwnerId() == $account->id()
            && ($account->hasPermission('delete own bibcite_reference')
              || $account->hasPermission("delete own $type bibcite_reference")))
            ->cachePerPermissions()->cachePerUser());
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create bibcite_reference')
      ->orIf(AccessResult::allowedIfHasPermission($account, 'create ' . $entity_bundle . ' bibcite_reference'));
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    $administrative_fields = ['uid', 'status', 'created'];
    $editing_administative = $operation == 'edit' && in_array($field_definition->getName(), $administrative_fields, TRUE);
    $updating_revision = $operation == 'update' && $field_definition->getName() === 'revision_id';
    if ($editing_administative || $updating_revision) {
      return AccessResult::allowedIfHasPermission($account, 'administer bibcite_reference');
    }
    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

}
