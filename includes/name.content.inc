<?php

/**
 * @file
 * Provides additional Field functionality for the name module.
 *
 * Most of these functions are related to setting configuration for the field,
 * instance and formatter.
 */

/* ----------------------------- Field Code --------------------------------- */

/**
 * Implements hook_field_settings_form().
 */
function _name_field_settings_form($field, $instance, $has_data) {
  $settings = $field->settings;
  $form = array(
    '#tree' => TRUE,
    '#element_validate' => array('_name_field_settings_form_validate'),
  );

  $components = _name_translations();
  $form['components'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Components'),
    '#default_value' => $settings['components'],
    '#required' => TRUE,
    '#description' => t('Only selected components will be activated on this field. All non-selected components / component settings will be ignored.'),
    '#options' => $components,
    '#element_validate' => array('_name_field_minimal_component_requirements'),
  );

  $form['minimum_components'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Minimum components'),
    '#default_value' => $settings['minimum_components'],
    '#required' => TRUE,
    '#element_validate' => array('_name_field_minimal_component_requirements'),
    '#description' => t('The minimal set of components required before the field is considered completed enough to save.'),
    '#options' => $components,
  );
  $form['labels'] = array();
  $form['max_length'] = array();
  $form['autocomplete_sources'] = array();
  $autocomplete_sources_options = array(
    'title' => t('Title options'),
    'generational' => t('Generational options'),
  );

  foreach ($components as $key => $title) {
    $min_length = 1;
    if ($has_data) {
      $min_length = $settings['max_length'][$key];
      if ($field['storage']['type'] == 'field_sql_storage') {
        try {
          $table = 'field_data_' . $field['field_name'];
          $column = $field['storage']['details']['sql'][FIELD_LOAD_CURRENT]
            [$table][$key];
          $min_length = db_query("SELECT MAX(CHAR_LENGTH({$column})) AS len FROM {$table}")->fetchField();
          if ($min_length < 1) {
            $min_length = 1;
          }
        }
        catch (Exception $e) {
        }
      }
    }
    $form['max_length'][$key] = array(
      '#type' => 'textfield',
      '#title' => t('Maximum length for !title', array('!title' => $title)),
      '#default_value' => $settings['max_length'][$key],
      '#required' => TRUE,
      '#size' => 10,
      '#min_size' => $min_length,
      '#description' => t('The maximum length of the field in characters. This must be between !min and 255.', array('!min' => $min_length)),
      '#element_validate' => array('_name_validate_varchar_range'),
    );
    $form['labels'][$key] = array(
      '#type' => 'textfield',
      '#title' => t('Label for !title', array('!title' => $title)),
      '#default_value' => $settings['labels'][$key],
      '#required' => TRUE,
    );
    $form['autocomplete_source'][$key] = array(
      '#type' => 'checkboxes',
      '#title' => t('Autocomplete options'),
      '#default_value' => $settings['autocomplete_source'][$key],
      '#description' => t("This defines what autocomplete sources are available to the field."),
      '#options' => $autocomplete_sources_options,
    );
    if ($key != 'title') {
      unset($form['autocomplete_source'][$key]['#options']['title']);
    }
    if ($key != 'generational') {
      unset($form['autocomplete_source'][$key]['#options']['generational']);
    }
    $form['autocomplete_separator'][$key] = array(
      '#type' => 'textfield',
      '#title' => t('Autocomplete separator for !title', array('!title' => $title)),
      '#default_value' => $settings['autocomplete_separator'][$key],
      '#size' => 10,
    );
  }

  $form['allow_family_or_given'] = array(
    '#type' => 'checkbox',
    '#title' => t('Allow a single valid given or family value to fulfill the minimum component requirements for both given and family components.'),
    '#default_value' => !empty($settings['allow_family_or_given']),
  );

  // TODO - Grouping & grouping sort
  // TODO - Allow reverse free tagging back into the vocabulary.
  $title_options = implode("\n", array_filter(explode("\n", $settings['title_options'])));
  $form['title_options'] = array(
    '#type' => 'textarea',
    '#title' => t('!title options', array('!title' => $components['title'])),
    '#default_value' => $title_options,
    '#required' => TRUE,
    '#description' => t("Enter one !title per line. Prefix a line using '--' to specify a blank value text. For example: '--Please select a !title'.", array('!title' => $components['title'])),
  );
  $generational_options = implode("\n", array_filter(explode("\n", $settings['generational_options'])));
  $form['generational_options'] = array(
    '#type' => 'textarea',
    '#title' => t('!generational options', array('!generational' => $components['generational'])),
    '#default_value' => $generational_options,
    '#required' => TRUE,
    '#description' => t("Enter one !generational suffix option per line. Prefix a line using '--' to specify a blank value text. For example: '----'.", array('!generational' => $components['generational'])),
  );
  if (module_exists('taxonomy')) {
    // TODO - Make the labels more generic.
    // Generational suffixes may be also imported from one or more vocabularies
    // using the tag '[vocabulary:xxx]', where xxx is the vocabulary id. Terms
    // that exceed the maximum length of the generational suffix are not added
    // to the options list.
    $form['title_options']['#description'] .= ' ' . t("%label_plural may be also imported from one or more vocabularies using the tag '[vocabulary:xxx]', where xxx is the vocabulary machine-name or id. Terms that exceed the maximum length of the %label are not added to the options list.",
        array('%label_plural' => t('Titles'), '%label' => t('Title')));
    $form['generational_options']['#description'] .= ' ' . t("%label_plural may be also imported from one or more vocabularies using the tag '[vocabulary:xxx]', where xxx is the vocabulary machine-name or id. Terms that exceed the maximum length of the %label are not added to the options list.",
        array('%label_plural' => t('Generational suffixes'), '%label' => t('Generational suffix')));
  }
  $sort_options = is_array($settings['sort_options']) ? $settings['sort_options'] : array(
    'title' => 'title',
    'generational' => '',
  );
  $form['sort_options'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Select field sort options'),
    '#default_value' => $sort_options,
    '#description' => t("This enables sorting on the options after the vocabulary terms are added and duplicate values are removed."),
    '#options' => _name_translations(array('title' => '', 'generational' => '')),
  );

  return $form;
}

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

// TODO - hook_field_views_data() if required

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
  $options = array_filter(explode("\n", $fs[$key . '_options']));
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

/* --------------------------- Helper Functions ----------------------------- */

/**
 * Helper form element validator: integer > 0 && <= 255.
 */
function _name_validate_varchar_range($element, &$form_state) {
  $value = $element['#value'];
  $min = isset($element['#min_size']) ? $element['#min_size'] : 1;
  if ($value !== '' && (!is_numeric($value) || intval($value) != $value || $value < $min || $value > 255)) {
    form_error($element, t('%name must be a positive integer between !min and 255.', array('%name' => $element['#title'], '!min' => $min)));
  }
}
