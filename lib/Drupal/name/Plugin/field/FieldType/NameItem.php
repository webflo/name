<?php

/**
 * @file
 * Contains \Drupal\name\Plugin\Field\FieldType\NameItem.
 */

namespace Drupal\name\Plugin\Field\FieldType;

use Drupal\Core\Field\ConfigFieldItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\LegacyConfigFieldItem;
use Drupal\field\FieldInterface;

/**
 * Plugin implementation of the 'name' field type.
 *
 * @FieldType(
 *   id = "name",
 *   label = @Translation("Name"),
 *   description = @Translation("Stores real name."),
 *   instance_settings = {
 *     "title" = "1"
 *   },
 *   default_widget = "name_default",
 *   default_formatter = "name_default"
 * )
 */
class NameItem extends ConfigFieldItemBase {

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
    if (!isset(self::$propertyDefinitions)) {
      self::$propertyDefinitions['given'] = array(
        'type' => 'string',
        'label' => t('Given'),
      );
    }
    return self::$propertyDefinitions;
  }

  public function settingsForm(array $form, array &$form_state, $has_data) {
    $foo = TRUE;
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
      $autocomplete_sources_options = array();
      if (module_exists('namedb')) {
        $autocomplete_sources_options['namedb'] = t('Names DB');
      }
      $autocomplete_sources_options['title'] = t('Title options');
      $autocomplete_sources_options['generational'] = t('Generational options');
      // TODO: Placing in the to hard basket for the time being!
      //$autocomplete_sources_options['data'] = t('Data');

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

  public function instanceSettingsForm(array $form, array &$form_state) {
    debug("BAR");
  }


  /**
   * Returns the schema for the field.
   *
   * This method is static, because the field schema information is needed on
   * creation of the field. No field instances exist by then, and it is not
   * possible to instantiate a FieldItemInterface object yet.
   *
   * @param \Drupal\field\FieldInterface $field
   *   The field definition.
   *
   * @return array
   *   An associative array with the following key/value pairs:
   *   - columns: An array of Schema API column specifications, keyed by column
   *     name. This specifies what comprises a value for a given field. For
   *     example, a value for a number field is simply 'value', while a value
   *     for a formatted text field is the combination of 'value' and 'format'.
   *     It is recommended to avoid having the column definitions depend on
   *     field settings when possible. No assumptions should be made on how
   *     storage engines internally use the original column name to structure
   *     their storage.
   *   - indexes: (optional) An array of Schema API index definitions. Only
   *     columns that appear in the 'columns' array are allowed. Those indexes
   *     will be used as default indexes. Callers of field_create_field() can
   *     specify additional indexes or, at their own risk, modify the default
   *     indexes specified by the field-type module. Some storage engines might
   *     not support indexes.
   *   - foreign keys: (optional) An array of Schema API foreign key
   *     definitions. Note, however, that the field data is not necessarily
   *     stored in SQL. Also, the possible usage is limited, as you cannot
   *     specify another field as related, only existing SQL tables,
   *     such as {taxonomy_term_data}.
   */
  public static function schema(FieldInterface $field) {
    module_load_include('module', 'name');
    $columns = array();
    foreach (_name_translations() as $key => $title) {
      $columns[$key] = array(
        'type' => 'varchar',
        'length' => 255,
        'not null' => FALSE,
      );
    }
    return array(
      'columns' => $columns,
      'indexes' => array(
        'given' => array('given'),
        'family' => array('family'),
      ),
    );
  }
}
