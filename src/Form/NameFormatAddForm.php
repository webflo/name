<?php

/**
 * @file
 * Contains \Drupal\name\Form\NameFormatAddForm.
 */

namespace Drupal\name\Form;

/**
 * Provides a form controller for adding a name format.
 */
class NameFormatAddForm extends NameFormatFormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save format');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    parent::submit($form, $form_state);
    drupal_set_message(t('Custom name format added.'));
  }

}
