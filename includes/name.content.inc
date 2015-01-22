<?php
use Drupal\Component\Utility\Unicode;

/**
 * @file
 * Provides additional Field functionality for the name module.
 *
 * Most of these functions are related to setting configuration for the field,
 * instance and formatter.
 */

/* ----------------------------- Widget Code -------------------------------- */
function _name_field_get_options($fs, $key) {
  $options = $fs[$key . '_options'];
  foreach ($options as $index => $opt) {
    if (preg_match('/^\[vocabulary:([0-9a-z\_]{1,})\]/', trim($opt), $matches)) {
      unset($options[$index]);
      if (Drupal::moduleHandler()->moduleExists('taxonomy')) {
        $vocabulary = entity_load('taxonomy_vocabulary', $matches[1]);
        if (isset($vocabulary->vid)) {
          $max_length = isset($fs['max_length'][$key]) ? $fs['max_length'][$key] : 255;
          foreach (taxonomy_get_tree($vocabulary->vid) as $term) {
            if (Unicode::strlen($term->name) <= $max_length) {
              $options[] = $term->name;
            }
          }
        }
      }
    }
  }
  // Options could come from multiple sources, filter duplicates.
  $options = array_unique($options);

  if (isset($fs['sort_options']) && !empty($fs['sort_options'][$key])) {
    natcasesort($options);
  }
  $default = FALSE;
  foreach ($options as $index => $opt) {
    if (strpos($opt, '--') === 0) {
      unset($options[$index]);
      $default = Unicode::substr($opt, 2);
    }
  }
  $options = array_map('trim', $options);
  $options = array_combine($options, $options);
  if ($default !== FALSE) {
    $options = array('' => $default) + $options;
  }
  return $options;
}
