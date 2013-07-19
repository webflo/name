<?php

/**
 * @file
 * Contains \Drupal\name\Plugin\Menu\LocalTask\NameFormatListTask.
 */

namespace Drupal\name\Plugin\Menu\LocalTask;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Menu\LocalTaskBase;
use Drupal\Core\Annotation\Menu\LocalTask;

/**
 * @LocalTask(
 *   id = "name_format_list_tab",
 *   route_name = "name_format_list",
 *   title = @Translation("Custom formats"),
 *   tab_root_id = "name_format_list_tab",
 *   weight = -10
 * )
 */
class NameFormatListTask extends LocalTaskBase {

}
