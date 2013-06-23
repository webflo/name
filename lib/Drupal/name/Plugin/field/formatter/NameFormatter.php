<?php

/**
 * @file
 * Definition of Drupal\number\Plugin\field\formatter\NameFormatter.
 */

namespace Drupal\name\Plugin\field\formatter;

use Drupal\field\Annotation\FieldFormatter;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin implementation of the 'name' formatter.
 *
 * The 'Default' formatter is different for integer fields on the one hand, and
 * for decimal and float fields on the other hand, in order to be able to use
 * different settings.
 *
 * @FieldFormatter(
 *   id = "name",
 *   module = "name",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "name",
 *   },
 *   settings = {
 *     "output" = "default",
 *     "format" = "default",
 *     "multiple" = "default",
 *     "multiple_delimiter" = ", ",
 *     "multiple_and" = "text",
 *     "multiple_delimiter_precedes_last" = "never",
 *     "multiple_el_al_min" = "3",
 *     "multiple_el_al_first" = "1"
 *   }
 * )
 */
class NameFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $field_name = explode('.', $this->fieldDefinition['id']);
    $field_name = end($field_name);

    $element = array();

    $element['format'] = array(
      '#type' => 'select',
      '#title' => t('Name format'),
      '#default_value' => $this->getSetting('format'),
      '#options' => array('default' => t('Default')) + name_get_custom_format_options(),
      '#required' => TRUE,
    );

    $element['markup'] = array(
      '#type' => 'checkbox',
      '#title' => t('Markup'),
      '#default_value' => $this->getSetting('markup'),
      '#description' => t('This option wraps the individual components of the name in SPAN elements with corresponding classes to the component.'),
    );

    $element['output'] = array(
      '#type' => 'radios',
      '#title' => t('Output'),
      '#default_value' => $this->getSetting('output'),
      '#options' => _name_formatter_output_options(),
      '#description' => t('This option provides additional options for rendering the field. <strong>Normally, using the "Raw value" option would be a security risk.</strong>'),
      '#required' => TRUE,
    );

    $element['multiple'] = array(
      '#type' => 'radios',
      '#title' => t('Multiple format options'),
      '#default_value' => $this->getSetting('multiple'),
      '#options' => _name_formatter_multiple_options(),
      '#required' => TRUE,
    );

    $base = array(
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][multiple]"]' => array('value' => 'inline_list'),
        ),
      ),
      '#prefix' => '<div style="padding: 0 2em;">',
      '#suffix' => '</div>',
    );
    // We can not nest this field, so use a prefix / suffix with padding to help
    // to provide context.
    $element['multiple_delimiter'] = $base + array(
      '#type' => 'textfield',
      '#title' => t('Delimiter'),
      '#default_value' => $this->getSetting('multiple_delimiter'),
      '#description' => t('This specifies the delimiter between the second to last and the last name.'),
    );
    $element['multiple_and'] = $base + array(
      '#type' => 'radios',
      '#title' => t('Last delimiter type'),
      '#options' => array(
        'text' => t('Textual (and)'),
        'symbol' => t('Ampersand (&amp;)'),
      ),
      '#default_value' => $this->getSetting('multiple_and'),
      '#description' => t('This specifies the delimiter between the second to last and the last name.'),
    );
    $element['multiple_delimiter_precedes_last'] = $base + array(
      '#type' => 'radios',
      '#title' => t('Standard delimiter precedes last delimiter'),
      '#options' => array(
        'never' => t('Never (i.e. "J. Doe and T. Williams")'),
        'always' => t('Always (i.e. "J. Doe<strong>,</strong> and T. Williams")'),
        'contextual' => t('Contextual (i.e. "J. Doe and T. Williams" <em>or</em> "J. Doe, S. Smith<strong>,</strong> and T. Williams")'),
      ),
      '#default_value' => $this->getSetting('multiple_delimiter_precedes_last'),
      '#description' => t('This specifies the delimiter between the second to last and the last name. Contextual means that the delimiter is only included for lists with three or more names.'),
    );
    $element['multiple_el_al_min'] = $base + array(
      '#type' => 'select',
      '#title' => t('Reduce list and append <em>el al</em>'),
      '#options' => array(0 => t('Never reduce')) + drupal_map_assoc(range(1, 20)),
      '#default_value' => $this->getSetting('multiple_el_al_min'),
      '#description' => t('This specifies a limit on the number of names to display. After this limit, names are removed and the abbrivation <em>et al</em> is appended. This Latin abbrivation of <em>et alii</em> means "and others".'),
    );
    $element['multiple_el_al_first'] = $base + array(
      '#type' => 'select',
      '#title' => t('Number of names to display when using <em>el al</em>'),
      '#options' => drupal_map_assoc(range(1, 20)),
      '#default_value' => $this->getSetting('multiple_el_al_first'),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary = array();

    $field_name = explode('.', $this->fieldDefinition['id']);
    $field_name = end($field_name);

    $machine_name = isset($settings['format']) ? $settings['format'] : 'default';
    if ($machine_name == 'default') {
      $summary[] = t('Format: Default');
    }
    else {
      $info = db_select('name_custom_format', 'n')
        ->fields('n')
        ->condition('machine_name', $machine_name)
        ->execute()
        ->fetchObject();
      if ($info) {
        $summary[] = t('Format: %format (@machine_name)', array(
          '%format' => $info->name,
          '@machine_name' => $info->machine_name
        ));
      }
      else {
        $summary[] = t('Format: <strong>Missing format.</strong><br/>This field will be displayed using the Default format.');
        $machine_name = 'default';
      }
    }
    // Provide an example of the selected format.
    module_load_include('admin.inc', 'name');
    $used_components = $this->getFieldSetting('components');
    $excluded_components = array_diff_key($used_components, _name_translations());
    $examples = name_example_names($excluded_components, $field_name);
    if ($examples && $example = array_shift($examples)) {
      $format = name_get_format_by_machine_name($machine_name);
      $formatted = check_plain(name_format($example, $format));
      if (empty($formatted)) {
        $formatted = '<em>&lt;&lt;empty&gt;&gt;</em>';
      }
      $summary[] = t('Example: !example', array(
        '!example' => $formatted
      ));
    }

    $summary[] = t('Markup: @yesno', array(
      '@yesno' => empty($settings['markup']) ? t('no') : t('yes')
    ));
    $output_options = _name_formatter_output_options();
    $output = empty($settings['output']) ? 'default' : $settings['output'];
    $summary[] = t('Output: @format', array(
      '@format' => $output_options[$output],
    ));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(EntityInterface $entity, $langcode, array $items) {
    $elements = array();

    $settings = $this->settings;
    $type = empty($settings['output']) ? 'default' : $settings['output'];
    $format = isset($settings['format']) ? $settings['format'] : 'default';

    $format = name_get_format_by_machine_name($format);
    if (empty($format)) {
      $format = name_get_format_by_machine_name('default');
    }

    foreach ($items as $delta => $item) {
      // We still have raw user input here unless the markup flag has been used.
      $value = name_format($item, $format, array(
        'object' => $entity,
        'type' => $entity->entityType(),
        'markup' => !empty($display['settings']['markup']
        )
      ));
      if (empty($display['settings']['markup'])) {
        $elements[$delta] = array(
          '#markup' => _name_value_sanitize($value, NULL, $type)
        );
      }
      else {
        $elements[$delta] = array('#markup' => $value);
      }
    }

    if (isset($settings['multiple']) && $settings['multiple'] == 'inline_list') {
      $items = array();
      foreach (element_children($elements) as $delta) {
        if (!empty($elements[$delta]['#markup'])) {
          $items[] = $elements[$delta]['#markup'];
          unset($elements[$delta]);
        }
      }

      if (!empty($items)) {
        $elements[0]['#markup'] = theme('name_item_list', array(
          'items' => $items,
          'settings' => $settings
        ));
      }
    }

    return $elements;
  }

  /**
   * Formats a name.
   *
   * @param mixed $name
   *   The numeric value.
   *
   * @return string
   *   The formatted name.
   */
  protected function nameFormat($name) {
    print_r($name);
  }
}
