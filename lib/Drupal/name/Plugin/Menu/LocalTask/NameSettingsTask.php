<?php

/**
 * @file
 * Contains \Drupal\name\Plugin\Menu\LocalTask\NameSettingsTask.
 */

namespace Drupal\name\Plugin\Menu\LocalTask;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Menu\LocalTaskBase;
use Drupal\Core\Annotation\Menu\LocalTask;

/**
 * @LocalTask(
 *   id = "name_settings_tab",
 *   route_name = "name_settings",
 *   title = @Translation("Settings"),
 *   tab_root_id = "name_format_list_tab",
 *   weight = 20
 * )
 */
class NameSettingsTask extends LocalTaskBase {

}
