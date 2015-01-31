<?php
/**
 * Created by PhpStorm.
 * User: fweber
 * Date: 31.01.15
 * Time: 21:08
 */

namespace Drupal\name\Tests;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

trait NameTestTrait {

  public function createNameField($field_name, $entity_type, $bundle) {
    FieldStorageConfig::create(array(
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'name',
    ))
    ->save();

    $field_config = FieldConfig::create(array(
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'name',
      'bundle' => $bundle,
    ));

    $field_config->save();
    return $field_config;
  }

}
