<?php

/**
 * @file
 * Contains \Drupal\name\LanguageAccessController.
 */

namespace Drupal\name;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class NameFormatAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    switch ($operation) {
      case 'create':
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'administer site configuration');
      case 'delete':
        if ($entity->isLocked()) {
          return AccessResult::forbidden();
        }
        return AccessResult::allowedIfHasPermission($account, 'administer site configuration');
    }
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
