<?php

/**
 * @file
 * Contains \Drupal\name\Type\IntegerItem.
 */

namespace Drupal\name\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'name_field' entity field item.
 */
class NameItem extends FieldItemBase {

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

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['given'] = array(
        'type' => 'string',
        'label' => t('Given'),
      );
    }
    return static::$propertyDefinitions;
  }
}
