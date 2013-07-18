<?php

/**
 * @file
 * Contains \Drupal\name\LanguageAccessController.
 */

namespace Drupal\name;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;

class NameFormatAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = Language::LANGCODE_DEFAULT, AccountInterface $account = NULL) {
    switch ($operation) {
      case 'create':
      case 'update':
      case 'delete':
        return !$entity->isLocked() && user_access('administer site configuration', $account);
        break;
    }
    return FALSE;
  }

}
