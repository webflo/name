<?php

/**
 * @file
 * Contains \Drupal\name\Plugin\Field\FieldWidget\NameWidget.
 */

namespace Drupal\name\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Plugin implementation of the 'name' widget.
 *
 * @FieldWidget(
 *   id = "name_default",
 *   module = "name",
 *   label = @Translation("Name field widget"),
 *   field_types = {
 *     "name"
 *   }
 * )
 */
class NameWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    module_load_include('inc', 'name', 'includes/name.content');
    $field_id = explode('.', $this->fieldDefinition->id);
    $field_name = end($field_id);
    $field_settings = $this->getFieldSettings();
    $instance['label'] = 'instance label';

    $element += array(
      '#type' => 'name',
      '#title' => String::checkPlain($instance['label']),
      '#label' => $instance['label'],
      '#components' => array(),
      '#minimum_components' => array_filter($field_settings['minimum_components']),
      '#allow_family_or_given' => !empty($field_settings['allow_family_or_given']),
      '#default_value' => isset($items[$delta]) ? $items[$delta]->getValue() : NULL,
      '#field' => $this,
      '#credentials_inline' => empty($field_settings['credentials_inline']) ? 0 : 1,
      '#component_css' => empty($field_settings['component_css']) ? '' : $field_settings['component_css'],
      '#component_layout' => empty($field_settings['component_layout']) ? 'default' : $field_settings['component_layout'],
      '#show_component_required_marker' => !empty($field_settings['show_component_required_marker']),
    );

    $components = array_filter($field_settings['components']);
    foreach (_name_translations() as $key => $title) {
      if (in_array($key, $components)) {
        $element['#components'][$key]['type'] = 'textfield';

        $size = !empty($field_settings['size'][$key]) ? $field_settings['size'][$key] : 60;
        $title_display = isset($field_settings['title_display'][$key]) ? $field_settings['title_display'][$key] : 'description';

        $element['#components'][$key]['title'] = String::checkPlain($field_settings['labels'][$key]);
        $element['#components'][$key]['title_display'] = $title_display;

        $element['#components'][$key]['size'] = $size;
        $element['#components'][$key]['maxlength'] = !empty($field_settings['max_length'][$key]) ? $field_settings['max_length'][$key] : 255;

        // Provides backwards compatibility with Drupal 6 modules.
        $field_type = ($key == 'title' || $key == 'generational') ? 'select' : 'text';
        $field_type = isset($field_settings['field_type'][$key])
            ? $field_settings['field_type'][$key]
            // Provides .
            : (isset($field_settings[$key . '_field']) ? $field_settings[$key . '_field'] : $field_type);

        if ($field_type == 'select') {
          $element['#components'][$key]['type'] = 'select';
          $element['#components'][$key]['size'] = 1;
          $element['#components'][$key]['options'] = _name_field_get_options($field_settings, $key);
        }
        elseif ($field_type == 'autocomplete') {
          if ($sources = $field_settings['autocomplete_source'][$key]) {
            $sources = array_filter($sources);
            if (!empty($sources)) {
              $element['#components'][$key]['autocomplete'] = array(
                '#autocomplete_route_name' => 'name.autocomplete',
                '#autocomplete_route_parameters' => array(
                  'field_name' => $this->fieldDefinition->id,
                  'component' => $key,
                ),
              );
            }
          }
        }

        if (isset($field_settings['inline_css'][$key]) && Unicode::strlen($field_settings['inline_css'][$key])) {
          $element['#components'][$key]['attributes'] = array(
            'style' => $field_settings['inline_css'][$key],
          );
        }
      }
      else {
        $element['#components'][$key]['exclude'] = TRUE;
      }
    }

    return $element;
  }
}
