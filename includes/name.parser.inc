<?php

/**
 * @file
 * Provides the functionality and information about the
 * Name module name parsing engine.
 */

/**
 * Provides help to the characters that are recognized in
 * the name_format() format parameter string.
 */
function theme_name_format_parameter_help() {
  $output = '<div>' . t('The following characters are recognized in the format parameter string:') . '</div>';
  $output .= '<dl>';
  foreach (name_replacement_tokens() as $token => $title) {
    $output .= "<dt>{$token}</dt><dd>{$title}</dd>";
  }
  $output .= '</dl>';

  return $output;
}
/**
 * Provides the tokens that the name parse can handle.
 *
 * Todo:: make the labels generic
 */
function name_replacement_tokens() {
  $tokens = array(
    't' => t('Title'),
    'g' => t('Given name'),
    'm' => t('Middle name(s)'),
    'f' => t('Family name'),
    'c' => t('Credentials'),
    's' => t('Generational suffix'),
    'x' => t('First letter given'),
    'y' => t('First letter middle'),
    'z' => t('First letter family'),
    'e' => t('Conditional: Either the given or family name. Given name is given preference.'),
    'E' => t('Conditional: Either the given or family name. Family name is given preference.'),
    'i' => t('Separator 1'),
    'j' => t('Separator 2'),
    'k' => t('Separator 3'),
    '\\' => t('You can prevent a character in the format string from being expanded by escaping it with a preceding backslash.'),
    'L' => t('Modifier: Converts the next token to all lowercase.'),
    'U' => t('Modifier: Converts the next token to all uppercase.'),
    'F' => t('Modifier: Converts the first letter to uppercase.'),
    'G' => t('Modifier: Converts the first letter of ALL words to uppercase.'),
    'T' => t('Modifier: Trims whitespace around the next token.'),
    'S' => t('Modifier: Ensures that the next token is safe for the display.'),
    '+' => t('Conditional: Insert the token if both the surrounding tokens are not empty.'),
    '-' => t('Conditional: Insert the token if the previous token is not empty'),
    '~' => t('Conditional: Insert the token if the previous token is empty'),
    '=' => t('Conditional: Insert the token if the next token is not empty.'),
    '^' => t('Conditional: Insert the token if the next token is empty.'),
    '|' => t('Conditional: Uses the previous token unless empty, otherwise it uses this token.'),
    '(' => t('Group: Start of token grouping.'),
    ')' => t('Group: End of token grouping.'),
    /*

 // Placeholders for token support insertion on the [object / key | entity / bundle].
 '1' => t('Token placeholder 1'),
 '2' => t('Token placeholder 2'),
 '3' => t('Token placeholder 3'),
 '4' => t('Token placeholder 4'),
 '5' => t('Token placeholder 5'),
 '6' => t('Token placeholder 6'),
 '7' => t('Token placeholder 7'),
 '8' => t('Token placeholder 8'),
 '9' => t('Token placeholder 9'),

 */
  );

  return $tokens;
}

function _name_generate_tokens($name_components, $settings = array()) {
  $name_components = (array) $name_components;
  $markup = !empty($settings['markup']);
  $name_components += array(
    'title' => '',
    'given' => '',
    'middle' => '',
    'family' => '',
    'credentials' => '',
    'generational' => '',
  );
  $settings = config('name.settings')->get();
  $tokens = array(
    't' => name_render_component($name_components['title'], 'title', $markup),
    'g' => name_render_component($name_components['given'], 'given', $markup),
    'm' => name_render_component($name_components['middle'], 'middle', $markup),
    'f' => name_render_component($name_components['family'], 'family', $markup),
    'c' => name_render_component($name_components['credentials'], 'credentials', $markup),
    's' => name_render_component($name_components['generational'], 'generational', $markup),
    'x' => name_render_component($name_components['given'], 'given', $markup, 'initial'),
    'y' => name_render_component($name_components['middle'], 'middle', $markup, 'initial'),
    'z' => name_render_component($name_components['family'], 'family', $markup, 'initial'),
    'i' => $settings['sep1'],
    'j' => $settings['sep2'],
    'k' => $settings['sep3'],
  );
  $given = $tokens['g'];
  $family = $tokens['f'];
  if ($given || $family) {
    $tokens += array(
      'e' => $given ? $given : $family,
      'E' => $family ? $family : $given,
    );
  }
  else {
    $tokens += array(
      'e' => NULL,
      'E' => NULL,
    );
  }
  return $tokens;
}
