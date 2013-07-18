<?php

/**
 * @file
 * Contains \Drupal\name\Form\NameFormatFormBase.
 */

namespace Drupal\name\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityFormController;

/**
 * Provides a base form controller for date formats.
 */
abstract class NameFormatFormBase extends EntityFormController implements EntityControllerInterface {

    /**
   * {@inheritdoc}
   */
  public function exists($entity_id, array $element,  array $form_state) {
    return entity_load('name_format', $entity_id);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $element = parent::form($form, $form_state);

    $element['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#default_value' => $this->entity->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    $element['id'] = array(
      '#type' => 'machine_name',
      '#title' => t('Machine-readable name'),
      '#description' => t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
      ),
    );

    $element['pattern'] = array(
      '#type' => 'textfield',
      '#title' => t('Format'),
      '#default_value' => $this->entity->get('pattern'),
      '#maxlength' => 255,
      '#required' => TRUE,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $this->entity->save();
    $form_state['redirect'] = 'admin/config/regional/name';
  }

}
