<?php

/**
 * @file
 * Contains \Drupal\name\Type\IntegerItem.
 */

namespace Drupal\name\Type;

use Drupal\field\Plugin\field\field_type\LegacyConfigFieldItem;

/**
 * Defines the 'name_field' entity field item.
 */
class NameItem extends LegacyConfigFieldItem {

  /**
   * Definitions of the contained properties.
   *
   * @see IntegerItem::getPropertyDefinitions()
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    if (!isset(self::$propertyDefinitions)) {
      self::$propertyDefinitions['given'] = array(
        'type' => 'string',
        'label' => t('Given'),
      );
    }
    return self::$propertyDefinitions;
  }


}
