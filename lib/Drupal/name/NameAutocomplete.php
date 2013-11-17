<?php

/**
 * @file
 * Contains \Drupal\name\NameAutocomplete.
 */

namespace Drupal\name;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\field\Entity\FieldInstance;

/**
 * Defines a helper class to get name field autocompletion results.
 */
class NameAutocomplete {

  /**
   * The database connection to query for the name names.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The config factory to get the anonymous name name.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a NameAutocomplete object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to query for the name values.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(Connection $connection, ConfigFactory $config_factory) {
    $this->connection = $connection;
    $this->configFactory = $config_factory;
  }

  /**
   * Get matches for the autocompletion of name components.
   *
   * @param \Drupal\field\Entity\FieldInstance $field
   *   The field instance.
   *
   * @param $target
   *   The name field component.
   *
   * @param string $string
   *   The string to match for the name field component.
   *
   * @return array
   *   An array containing the matching values.
   */
  public function getMatches(FieldInstance $field, $target, $string) {
    $matches = array();
    $limit = 10;
    $all_components = array(
      'given',
      'middle',
      'family',
      'title',
      'credentials',
      'generational'
    );

    if ($string) {
      $settings = $field->getFieldSettings();

      $action = array();
      switch ($target) {
        case 'name':
          $action['components'] = drupal_map_assoc(array(
            'given',
            'middle',
            'family'
          ));
          break;

        case 'name-all':
          $action['components'] = drupal_map_assoc($all_components);
          break;

        case 'title':
        case 'given':
        case 'middle':
        case 'family':
        case 'credentials':
        case 'generational':
          $action['components'] = array($target => $target);
          break;

        default:
          $action['components'] = array();
          foreach (explode('-', $target) as $component) {
            if (in_array($component, array(
              'title',
              'given',
              'middle',
              'family',
              'credentials',
              'generational'
            ))
            ) {
              $action['components'][$component] = $component;
            }
          }
          break;

      }

      $action['source'] = array(
        'title' => array(),
        'generational' => array(),
      );

      $action['separater'] = '';

      foreach ($action['components'] as $component) {
        if (empty($settings['autocomplete_source'][$component])) {
          unset($action['components'][$component]);
        }
        else {
          $sep = (string) $settings['autocomplete_separator'][$component];
          if (empty($sep)) {
            $sep = ' ';
          }
          for ($i = 0; $i <= count($sep); $i++) {
            if (strpos($action['separater'], $sep{$i}) === FALSE) {
              $action['separater'] .= $sep{$i};
            }
          }
          $found_source = FALSE;

          foreach ((array) $settings['autocomplete_source'][$component] as $src) {
            if ($src == 'title' || $src == 'generational') {
              if (!$field || $component != $src) {
                continue;
              }
            }
            $found_source = TRUE;
            $action['source'][$src][] = $component;
          }

          if (!$found_source) {
            unset($action['components'][$component]);
          }
        }
      }

      $pieces = preg_split('/[' . preg_quote($action['separater']) . ']+/', $string);

      // We should have nice clean parameters to query.
      if (!empty($pieces) && !empty($action['components'])) {
        $test_string = Unicode::strtolower(array_pop($pieces));
        $base_string = Unicode::substr($string, 0, drupal_strlen($string) - drupal_strlen($test_string));

        if ($limit > 0 && count($action['source']['title'])) {
          $field_settings = $field->getFieldSettings();
          $options = name_field_get_options($field_settings, 'title');
          foreach ($options as $key => $option) {
            if (strpos(Unicode::strtolower($key), $test_string) === 0 || strpos(Unicode::strtolower($option), $test_string) === 0) {
              $matches[$base_string . $key] = $key;
              $limit--;
            }
          }
        }

        if ($limit > 0 && count($action['source']['generational'])) {
          $field_settings = $field->getFieldSettings();
          $options = name_field_get_options($field_settings, 'generational');
          foreach ($options as $key => $option) {
            if (strpos(Unicode::strtolower($key), $test_string) === 0 || strpos(Unicode::strtolower($option), $test_string) === 0) {
              $matches[$base_string . $key] = $key;
              $limit--;
            }
          }
        }
      }
    }

    return $matches;
  }

}
