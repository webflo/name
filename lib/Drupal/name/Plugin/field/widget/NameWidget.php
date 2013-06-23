<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Plugin\field\widget\NameWidget.
 */

namespace Drupal\name\Plugin\field\widget;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Widget\WidgetBase;

/**
 * Plugin implementation of the 'name' widget.
 *
 * @Plugin(
 *   id = "name",
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
  public function formElement(array $items, $delta, array $element, $langcode, array &$form, array &$form_state) {
    module_load_include('inc', 'name', 'includes/name.content');
    return _name_field_widget_form($form, $form_state, $this->fieldDefinition, $this->fieldDefinition, $langcode, $items, $delta, $element);
  }
}
