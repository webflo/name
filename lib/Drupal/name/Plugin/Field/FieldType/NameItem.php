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
 *   settings = {
 *     "components" = {
 *       "title",
 *       "given",
 *       "middle",
 *       "family",
 *       "generational",
 *       "credentials"
 *     },
 *     "minimum_components" = {
 *       "given",
 *       "family",
 *     },
 *     "allow_family_or_given" = false,
 *     "labels" = {
 *       "title" = @Translation("Title", context = "Name field"),
 *       "given" = @Translation("Given", context = "Name field"),
 *       "middle" = @Translation("Middle name(s)", context = "Name field"),
 *       "family" = @Translation("Family", context = "Name field"),
 *       "generational" = @Translation("Generational", context = "Name field"),
 *       "credentials" = @Translation("Credentials", context = "Name field")
 *     },
 *     "max_length" = {
 *       "title" = 31,
 *       "given" = 63,
 *       "middle" = 127,
 *       "family" = 63,
 *       "generational" = 15,
 *       "credentials" = 255
 *     },
 *     "autocomplete_source" = {
 *       "title" = {
 *         "title"
 *       },
 *       "given" = {
 *       },
 *       "middle" = {
 *       },
 *       "family" = {
 *       },
 *       "generational" = {
 *         "generation"
 *       },
 *       "credentials" = {
 *       },
 *     },
 *     "autocomplete_separator" = {
 *       "title" = " ",
 *       "given" = " -",
 *       "middle" = " -",
 *       "family" = " -",
 *       "generational" = " ",
 *       "credentials" = ", ",
 *     },
 *     "title_options" = {
 *       @Translation("-- --"),
 *       @Translation("Mr."),
 *       @Translation("Mrs."),
 *       @Translation("Miss"),
 *       @Translation("Ms."),
 *       @Translation("Dr."),
 *       @Translation("Prof.")
 *     },
 *     "generational_options" = {
 *       @Translation("-- --"),
 *       @Translation("Jr."),
 *       @Translation("Sr."),
 *       @Translation("I"),
 *       @Translation("II"),
 *       @Translation("III"),
 *       @Translation("IV"),
 *       @Translation("V"),
 *       @Translation("VI"),
 *       @Translation("VII"),
 *       @Translation("VIII"),
 *       @Translation("IX"),
 *       @Translation("X")
 *     },
 *     "sort_options" = {
 *       "title"
 *     }
 *   },
 *   instance_settings = {
 *     "component_css" = "",
 *     "component_layout" = "default",
 *     "show_component_required_marker" = false,
 *     "credentials_inline" = false,
 *     "override_format" = "default",
 *     "field_type" = {
 *       "title" = "select",
 *       "given" = "text",
 *       "middle" = "text",
 *       "family" = "text",
 *       "generational" = "select",
 *       "credentials" = "text"
 *     },
 *     "size" = {
 *       "title" = "6",
 *       "given" = "20",
 *       "middle" = "20",
 *       "family" = "20",
 *       "generational" = "5",
 *       "credentials" = "35"
 *     },
 *     "title_display" = {
 *       "title" = "description",
 *       "given" = "description",
 *       "middle" = "description",
 *       "family" = "description",
 *       "generational" = "description",
 *       "credentials" = "description"
 *     },
 *     "inline_css" = {
 *       "title" = "",
 *       "given" = "",
 *       "middle" = "",
 *       "family" = "",
 *       "generational" = "",
 *       "credentials" = ""
 *     }
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
   * Definition of name field components
   *
   * @var array
   */
  protected static $components = array(
    'title',
    'given',
    'middle',
    'family',
    'generational',
    'credentials'
  );

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
   */
  public function getPropertyDefinitions() {
    if (!isset(self::$propertyDefinitions)) {
      self::$propertyDefinitions['title'] = array(
        'type' => 'string',
        'label' => t('Title'),
      );
      self::$propertyDefinitions['given'] = array(
        'type' => 'string',
        'label' => t('Given'),
      );
      self::$propertyDefinitions['middle'] = array(
        'type' => 'string',
        'label' => t('Middle name(s)'),
      );
      self::$propertyDefinitions['family'] = array(
        'type' => 'string',
        'label' => t('Family'),
      );
      self::$propertyDefinitions['generational'] = array(
        'type' => 'string',
        'label' => t('Generational'),
      );
      self::$propertyDefinitions['credentials'] = array(
        'type' => 'string',
        'label' => t('Credentials'),
      );
    }
    return self::$propertyDefinitions;
  }

  public function settingsForm(array $form, array &$form_state, $has_data) {
    /**
     * @var \Drupal\field\Entity\Field $field
     */
    $field = $form['#field'];

    /**
     * @todo: Remove $settings and use $field->getSetting("setting")
     */
    $settings = $field->getFieldSettings();

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
        // @todo: Port this feature to Drupal 8
        /*
        if ($field['storage']['type'] == 'field_sql_storage') {
          try {
            $table = 'field_data_' . $field['field_name'];
            $column = $field['storage']['details']['sql'][FIELD_LOAD_CURRENT]
            [$table][$key];
            $min_length = db_query("SELECT MAX(CHAR_LENGTH({$column})) AS len FROM {$table}")->fetchField();
            if ($min_length < 1) {
              $min_length = 1;
            }
          } catch (Exception $e) {
          }
        }
        */
      }
      $form['max_length'][$key] = array(
        '#type' => 'number',
        '#min' => $min_length,
        '#max' => 255,
        '#title' => t('Maximum length for !title', array('!title' => $title)),
        '#default_value' => $settings['max_length'][$key],
        '#required' => TRUE,
        '#size' => 10,
        '#description' => t('The maximum length of the field in characters. This must be between !min and 255.', array('!min' => $min_length)),

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
    $title_options = implode("\n", array_filter($settings['title_options']));
    $form['title_options'] = array(
      '#type' => 'textarea',
      '#title' => t('!title options', array('!title' => $components['title'])),
      '#default_value' => $title_options,
      '#required' => TRUE,
      '#description' => t("Enter one !title per line. Prefix a line using '--' to specify a blank value text. For example: '--Please select a !title'.", array('!title' => $components['title'])),
      '#submit' => array(
        array($this, 'submitTitleOptions')
      )
    );
    $generational_options = implode("\n", array_filter($settings['generational_options']));
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
          array(
            '%label_plural' => t('Generational suffixes'),
            '%label' => t('Generational suffix')
          ));
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
      '#options' => _name_translations(array(
        'title' => '',
        'generational' => ''
      )),
    );

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function isEmpty() {
    foreach ($this->properties as $property) {
      $definition = $property->getDefinition();
      if (empty($definition['computed']) && $property->getValue() !== NULL) {
        return FALSE;
      }
    }
    if (isset($this->values)) {
      foreach ($this->values as $name => $value) {
        // Title & generational have no meaning by themselves.
        if ($name == 'title' || $name == 'generational') {
          continue;
        }
        if (isset($value) && !isset($this->properties[$name])) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
   * Field settings form submit handler. Registered in
   * name_form_field_ui_field_edit_form_alter().
   *
   * Convert the multi line user input an array.
   */
  public static function submitFieldSettings(array $form, array &$form_state) {
    $settings = &$form_state['values']['field']['settings'];
    $settings['title_options'] = array_filter(array_map('trim', explode("\n", $settings['title_options'])));
    $settings['generational_options'] = array_filter(array_map('trim', explode("\n", $settings['generational_options'])));
  }

  /**
   * {@inheritDoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    /**
     * @var \Drupal\field\Entity\FieldInstance $field_instance
     */
    $field_instance = $this->getParent()->getFieldDefinition();

    /**
     * @todo: Remove $settings and use $field->getSetting("setting")
     */
    $settings = $field_instance->getFieldSettings();

    $components = _name_translations();
    $form = array(
      'size' => array(),
      'title_display' => array(),
    );

    $field_options = array(
      'select' => t('Drop-down'),
      'text' => t('Text field'),
      'autocomplete' => t('Autocomplete')
    );

    foreach ($components as $key => $title) {
      $form['field_type'][$key] = array(
        '#type' => 'radios',
        '#title' => t('!title field type', array('!title' => $components['title'])),
        '#default_value' => $settings['field_type'][$key],
        '#required' => TRUE,
        '#options' => $field_options,
      );

      if (!($key == 'title' || $key == 'generational')) {
        unset($form['field_type'][$key]['#options']['select']);
      }

      $form['size'][$key] = array(
        '#type' => 'number',
        '#min' => 1,
        '#max' => 255,
        '#title' => t('HTML size property for !title', array('!title' => $title)),
        '#default_value' => $settings['size'][$key],
        '#required' => FALSE,
        '#size' => 10,
        '#description' => t('The maximum length of the field in characters. This must be between 1 and 255.'),
      );

      $form['title_display'][$key] = array(
        '#type' => 'radios',
        '#title' => t('Label display for !title', array('!title' => $title)),
        '#default_value' => $settings['title_display'][$key],
        '#options' => array(
          'title' => t('above'),
          'description' => t('below'),
          'none' => t('hidden'),
        ),
        '#description' => t('This controls how the label of the component is displayed in the form.'),
      );

      $form['inline_css'][$key] = array(
        '#type' => 'textfield',
        '#title' => t('Additional inline styles for !title input element.', array('!title' => $title)),
        '#default_value' => $settings['inline_css'][$key],
        '#size' => 8,
      );
    }

    $form['component_css'] = array(
      '#type' => 'textfield',
      '#title' => t('Component separator CSS'),
      '#default_value' => $field_instance->getFieldSetting('component_css'),
      '#description' => t('Use this to override the default CSS used when rendering each component. Use "&lt;none&gt;" to prevent the use of inline CSS.'),
    );

    $items = array(
      t('The order for Asian names is Family Middle Given Title'),
      t('The order for Eastern names is Title Family Given Middle'),
      t('The order for Western names is Title First Middle Surname'),
    );
    $layout_description = t('<p>This controls the order of the widgets that are displayed in the form.</p>')
      . theme('item_list', array('items' => $items))
      . t('<p>Note that when you select the Asian names format, the Generational field is hidden and defaults to an empty string.</p>');
    $form['component_layout'] = array(
      '#type' => 'radios',
      '#title' => t('Language layout'),
      '#default_value' => $field_instance->getFieldSetting('component_layout'),
      '#options' => array(
        'default' => t('Western names'),
        'asian' => t('Asian names'),
        'eastern' => t('Eastern names'),
      ),
      '#description' => $layout_description,
    );
    $form['show_component_required_marker'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show component required marker'),
      '#default_value' => $field_instance->getFieldSetting('show_component_required_marker'),
      '#description' => t('Appends an asterisk after the component title if the component is required as part of a complete name.'),
    );
    $form['credentials_inline'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show the credentials inline'),
      '#default_value' => $field_instance->getFieldSetting('credentials_inline'),
      '#description' => t('The default position is to show the credentials on a line by themselves. This option overrides this to render the component inline.'),
    );

    // Add the overwrite user name option.
    if ($field_instance->entity_type == 'user' && $field_instance->bundle == 'user') {
      $preferred_field = config('name.settings')->get('user_preferred');
      $form['name_user_preferred'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use this field to override the users login name?'),
        '#default_value' => $preferred_field == $field_instance->field_name ? 1 : 0,
      );
      $form['override_format'] = array(
        '#type' => 'select',
        '#title' => t('User name override format to use'),
        '#default_value' => $field_instance->getFieldSetting('override_format'),
        '#options' => name_get_custom_format_options(),
      );
    }
    else {
      // We may extend this feature to Profile2 latter.
      $form['override_format'] = array(
        '#type' => 'value',
        '#value' => $field_instance->getFieldSetting('override_format'),
      );
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public static function schema(FieldInterface $field) {
    $columns = array();
    foreach (self::$components as $key) {
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
