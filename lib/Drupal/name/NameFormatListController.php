<?php

/**
 * @file
 * Drupal \Drupal\name\NameFormatListController.php
 */

namespace Drupal\name;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

class NameFormatListController extends ConfigEntityListController {

  public function buildHeader() {
    $row['label'] = t('Label');
    $row['id'] = t('Machine name');
    $row['format'] = t('Format');
    $row['examples'] = t('Examples');
    $row['operations'] = t('Operations');
    return $row;
  }


  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['format'] = $entity->get('pattern');
    $row['examples'] = implode('<br/>', $this->examples($entity));
    $operations = $this->buildOperations($entity);
    $row['operations']['data'] = $operations;
    return $row;
  }

  public function examples(EntityInterface $entity) {
    module_load_include('inc', 'name', 'name.admin');
    $examples = array();
    $example_names = \name_example_names();
    foreach ($example_names as $index => $example_name) {
      $formatted = check_plain(name_format($example_name, $entity->get('pattern')));
      if (empty($formatted)) {
        $formatted = '<em>&lt;&lt;empty&gt;&gt;</em>';
      }
      $examples[] = $formatted . " <sup>{$index}</sup>";
    }
    return $examples;
  }

}
