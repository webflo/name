<?php

/**
 * @file
 * Theme functions for name module.
 */

/**
 * Custom theme callback for the name_element.
 */
function theme_name_element($variables) {
  $element = $variables['element'];
  return drupal_render_children($element);
}

/**
 * Wrapper theming function for name_format().
 */
function theme_name_item($variables) {
  $variables['settings'] += array(
    'markup' => 0,
  );
  $format = name_get_format_by_machine_name($variables['format']);
  if (empty($format)) {
    $format = name_get_format_by_machine_name('default');
  }
  return name_format($variables['item'], $format, $variables['settings']);
}

/**
 * Themes a list of names.
 *
 * Note: This function expects a list of sanitised name items.
 */
function theme_name_item_list($variables) {
  $items = $variables['items'];
  if (empty($items)) {
    return '';
  }
  $original_count = count($items);
  if ($original_count == 1) {
    return array_pop($items);
  }
  $settings = $variables['settings'];
  $settings += array(
    'output' => 'default', // default, plain, or raw
    'multiple_delimiter' => ', ',
    'multiple_and' => 'text', // and or symbol
    'multiple_delimiter_precedes_last' => 'never', // contextual, always, never
    'multiple_el_al_min' => 3,
    'multiple_el_al_first' => 1,
  );
  $delimiter = $settings['multiple_delimiter'];
  if ($settings['output'] == 'default') {
    $etal = t('<em>et al</em>', array(), array('context' => 'name'));
    $delimiter = check_plain($delimiter);
  }
  else {
    $etal =t('et al', array(), array('context' => 'name'));
    if ($settings['output'] == 'plain') {
      $delimiter = strip_tags($delimiter);
    }
  }
  $t_args = array(
    '!delimiter' => $delimiter,
    '!etal' => $etal,
  );
  if ($original_count > $settings['multiple_el_al_min']) {
    $limit = min(array($settings['multiple_el_al_min'], $settings['multiple_el_al_first']));
    $items = array_slice($items, 0, $limit);
    if (count($items) == 1) {
      $t_args['!name'] = $items[0];
      return t('!name !etal', $t_args);
    }
    else {
      $t_args['!names'] = implode($delimiter . ' ', $items);
      return t('!names!delimiter !etal', $t_args);
    }
  }
  else {
    $t_args['!lastname'] = array_pop($items);
    $t_args['!names'] = implode($delimiter . ' ', $items);
    if ($settings['multiple_and'] == 'text') {
      $t_args['!and'] = t('and', array(), array('context' => 'name'));
    }
    else {
      $t_args['!and'] = $settings['output'] == 'default' ? '&amp' : '&';
    }

    // Strange rule from http://citationstyles.org/downloads/specification.html.
    if (($settings['multiple_delimiter_precedes_last'] == 'contextual' && $original_count > 2)
        || $settings['multiple_delimiter_precedes_last'] == 'always') {
      return t('!names!delimiter !and !lastname', $t_args);
    }
    else {
      return t('!names !and !lastname', $t_args);
    }
  }
}


/**
* Returns HTML for a marker for required name components.
*
* @param $variables
*   An associative array containing:
*   - element: An associative array containing the properties of the component.
*
* @ingroup themeable
*/
function theme_name_component_required_marker($variables) {
  $base_element = $variables['base_element'];
  $components = $variables['components'];
  $component_key = $variables['component_key'];
  $name_translations = _name_translations();
  $title = empty($base_element['#title']) ? t('Name') : $base_element['#title'];
  if (!empty($base_element['#allow_family_or_given']) && ($component_key == 'given' || $component_key == 'family')) {
    $title_attribute = t('!given_title or !family_title is required when entering a !title.', array(
        '!given_title' => empty($components['given']['title']) ? $name_translations['given'] : $components['given']['title'],
        '!family_title' => empty($components['family']['title']) ? $name_translations['family'] : $components['family']['title'],
        '!title' => $title));
  }
  else {
    $component_title = empty($components[$component_key]['title']) ? $name_translations[$component_key] : $components[$component_key]['title'];
    $title_attribute = t('!component_title is required when entering a !title.', array('!component_title' => $component_title, '!title' => $title));
  }
  // Both field label and component labels have already been sanitized.
  return ' <span class="name-required-component-marker" title="' . $title_attribute . '">' . config('name.settings')->get('component_required_marker') . '</span>';
}
