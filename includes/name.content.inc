<?php

/**
 * @file
 * Provides additional Field functionality for the name module.
 *
 * Most of these functions are related to setting configuration for the field,
 * instance and formatter.
 */

/**
 * Implements the validation callback for the name_field_settings_form() form.
 *
 * This is an #element_validate callback.
 *
 * TODO - Ensure that the #parent path is correct in all instances. This avoids
 *        the need to pull out individual values from the $elements[xx][#value].
 */
function _name_field_settings_form_validate($elements, &$form_state, $form) {
  $values = $form_state['values']['field']['settings'];

  // Validates options against the title / generational sizes.
  _name_options_validate($values['title_options'], $values['max_length']['title'],
      t('Title options'), 'field][settings][title_options');
  _name_options_validate($values['generational_options'], $values['max_length']['generational'],
      t('Generational options'), 'field][settings][generational_options');

  // Validates that a minimum component is checked when that component is not used.
  _name_field_components_validate($values['components'],
      $values['minimum_components'], 'field][settings][minimum_components');
}

/**
 * Checks that individual values in the defined options list do not exceed the
 * limits placed on the component.
 */
function _name_options_validate($options, $max, $label, $error_element) {
  $values = array_filter(explode("\n", $options));
  $long_options = array();
  $valid_options = array();
  $default_options = array();
  foreach ($values as $value) {
    $value = trim($value);
    // Blank option - anything goes!
    if (strpos($value, '--') === 0) {
      $default_options[] = $value;
    }
    // Simple checks on the taxonomy includes.
    elseif (preg_match('/^\[vocabulary:([0-9a-z\_]{1,})\]/', $value, $matches)) {
      if (!Drupal::moduleHandler()->moduleExists('taxonomy')) {
        form_set_error($error_element, t("The taxonomy module must be enabled before using the '%tag' tag in %label.",
            array('%tag' => $matches[0], '%label' => $label)));
      }
      elseif ($value !== $matches[0]) {
        form_set_error($error_element, t("The '%tag' tag in %label should be on a line by itself.",
            array('%tag' => $matches[0], '%label' => $label)));
      }
      else {
        $vocabulary = entity_load('taxonomy_vocabulary', $matches[1]);
        if (!$vocabulary) {
          form_set_error($error_element, t("The vocabulary '%tag' in %label could not be found.",
            array('%tag' => $matches[1], '%label' => $label)));
        }
      }
    }
    elseif (drupal_strlen($value) > $max) {
      $long_options[] = $value;
    }
    elseif (!empty($value)) {
      $valid_options[] = $value;
    }
  }
  if (count($long_options)) {
    form_set_error($error_element, t('The following options exceed the maximum allowed %label length: %options',
        array('%options' => implode(', ', $long_options), '%label' => $label)));
  }
  elseif (empty($valid_options)) {
    form_set_error($error_element, t('%label are required.',
        array('%label' => $label)));
  }
  elseif (count($default_options) > 1) {
    form_set_error($error_element, t('%label can only have one blank value assigned to it.',
        array('%label' => $label)));
  }
}

function _name_field_components_validate($components, $minimum, $error_element) {
  $diff = array_diff_key(array_filter($minimum), array_filter($components));
  if (count($diff)) {
    $components = array_intersect_key(_name_translations(), $diff);
    form_set_error($error_element . '][' . key($diff),
        t('%components can not be selected for %label when they are not selected for %label2.',
        array('%label' => t('Minimum components'), '%label2' => t('Components'),
        '%components' => implode(', ', $components))));
  }
}

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
            if (drupal_strlen($term->name) <= $max_length) {
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
      $default = drupal_substr($opt, 2);
    }
  }
  $options = drupal_map_assoc(array_map('trim', $options));
  if ($default !== FALSE) {
    $options = array('' => $default) + $options;
  }
  return $options;
}
