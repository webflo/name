<?php

/**
 * @file
 * Contains \Drupal\name\Plugin\Core\Entity\NameFormat.
 */

namespace Drupal\name\Plugin\Core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\name\NameFormatInterface;

/**
 * Defines the Name Format configuration entity class.
 *
 * @EntityType(
 *   id = "name_format",
 *   label = @Translation("Name format"),
 *   module = "name",
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "access" = "Drupal\name\NameFormatAccessController",
 *     "list" = "Drupal\name\NameFormatListController",
 *     "form" = {
 *       "add" = "Drupal\name\Form\NameFormatAddForm",
 *       "edit" = "Drupal\name\Form\NameFormatEditForm",
 *       "delete" = "Drupal\name\Form\NameFormatDeleteForm"
 *     }
 *   },
 *   config_prefix = "name.name_format",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class NameFormat extends ConfigEntityBase implements NameFormatInterface {

  /**
   * The name format machine name.
   *
   * @var string
   */
  public $id;

  /**
   * The name format UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable name of the name format entity.
   *
   * @var string
   */
  public $label;

  /**
   * The name format pattern.
   *
   * @var array
   */
  protected $pattern;

  /**
   * The locked status of this name format.
   *
   * @var bool
   */
  protected $locked = FALSE;

  /**
   * {@inheritdoc}
   */
  public function uri() {
    return array(
      'path' => 'admin/config/regional/name/manage/' . $this->id(),
      'options' => array(
        'entity_type' => $this->entityType,
        'entity' => $this,
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getExportProperties() {
    $properties = parent::getExportProperties();
    $names = array(
      'locked',
      'pattern',
    );
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getPattern($type = NULL) {
    return isset($this->pattern[$type]) ? $this->pattern[$type] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function setPattern($pattern, $type = NULL) {
    $this->pattern[$type] = $pattern;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return (bool) $this->locked;
  }
}
