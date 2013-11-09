<?php

/**
 * @file
 * Drupal \Drupal\name\NameFormatListController.php
 */

namespace Drupal\name;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

class NameFormatListController extends ConfigEntityListController {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['label'] = t('Label');
    $row['id'] = t('Machine name');
    $row['format'] = t('Format');
    $row['examples'] = t('Examples');
    $row['operations'] = t('Operations');
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['format'] = $entity->get('pattern');
    $row['examples'] = implode('<br/>', $this->examples($entity));
    $operations = $this->buildOperations($entity);
    $row['operations']['data'] = $operations;
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function examples(EntityInterface $entity) {
    $examples = array();
    foreach ($this->nameExamples() as $index => $example_name) {
      $formatted = check_plain(NameFormatParser::parse($example_name, $entity->get('pattern')));
      if (empty($formatted)) {
        $formatted = '<em>&lt;&lt;empty&gt;&gt;</em>';
      }
      $examples[] = $formatted . " <sup>{$index}</sup>";
    }
    return $examples;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $render['list'] = parent::render();
    $render['help'] = _name_get_name_format_help_form();
    return $render;
  }

  /**
   * Help box.
   *
   * @return array
   */
  public function nameFormatHelp() {
    module_load_include('inc', 'name', 'name.admin');
    return _name_get_name_format_help_form();
  }

  /**
   * Example names.
   *
   * @return null
   */
  public function nameExamples() {
    module_load_include('inc', 'name', 'name.admin');
    return name_example_names();
  }
}
