<?php

/**
 * @file
 * Contains \Drupal\name\NameFieldTest.
 *
 * Tests for the name module.
 */

namespace Drupal\name\Tests;

use Drupal;

/**
 * Tests for the admin settings and custom format page.
 */
class NameFieldTest extends NameTestHelper {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'field',
    'field_sql_storage',
    'field_ui',
    'node',
    'name',
    'taxonomy'
  );

  public static function getInfo() {
    return array(
      'name' => 'Node Name Field',
      'description' => 'Various tests on creating a name field on a node.' ,
      'group' => 'Name',
    );
  }

  function setUp() {
    parent::setUp();

    // Create content-type: page
    $page = entity_create('node_type', array('type' => 'page', 'name' => 'Basic page'));
    $page->save();
  }

  /**
   * The most basic test. This should only fail if there is a change to the
   * Drupal API.
   */
  function testFieldEntry() {
    debug(entity_get_bundles());
    $this->drupalLogin($this->admin_user);

    $new_name_field = array(
      'fields[_add_new_field][label]' => 'Test name',
      'fields[_add_new_field][field_name]' => 'name_test',
      'fields[_add_new_field][type]' => 'name',
    );

    $this->drupalPost('admin/structure/types/manage/page/fields', $new_name_field, t('Save'));
    $this->resetAll();

    // Required test.
    $field_settings = array();
    foreach ($this->name_getFieldSettings() as $key => $value) {
      $field_settings[$key] = '';
    }
    foreach ($this->name_getFieldSettingsCheckboxes() as $key => $value) {
      $field_settings[$key] = FALSE;
    }
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
      $field_settings, t('Save field settings'));

    $n = _name_translations();
    $required_messages = array(
      t('Label for !field field is required.', array('!field' => $n['title'])),
      t('Label for !field field is required.', array('!field' => $n['given'])),
      t('Label for !field field is required.', array('!field' => $n['middle'])),
      t('Label for !field field is required.', array('!field' => $n['family'])),
      t('Label for !field field is required.', array('!field' => $n['generational'])),
      t('Label for !field field is required.', array('!field' => $n['credentials'])),

      t('Maximum length for !field field is required.', array('!field' => $n['title'])),
      t('Maximum length for !field field is required.', array('!field' => $n['given'])),
      t('Maximum length for !field field is required.', array('!field' => $n['middle'])),
      t('Maximum length for !field field is required.', array('!field' => $n['family'])),
      t('Maximum length for !field field is required.', array('!field' => $n['generational'])),
      t('Maximum length for !field field is required.', array('!field' => $n['credentials'])),
      t('!field options field is required.', array('!field' => $n['title'])),
      t('!field options field is required.', array('!field' => $n['generational'])),

      t('!field field is required.', array('!field' => t('Components'))),
      t('!field must have one of the following components: !components', array('!field' => t('Components'), '!components' => check_plain(implode(', ', array($n['given'], $n['family']))))),
      t('!field field is required.', array('!field' => t('Minimum components'))),
      t('!field must have one of the following components: !components', array('!field' => t('Minimum components'), '!components' => check_plain(implode(', ', array($n['given'], $n['family']))))),
    );
    foreach ($required_messages as $message) {
      $this->assertText($message);
    }
    $field_settings = array(
      'field[settings][components][title]' => FALSE,
      'field[settings][components][given]' => TRUE,
      'field[settings][components][middle]' => FALSE,
      'field[settings][components][family]' => TRUE,
      'field[settings][components][generational]' => FALSE,
      'field[settings][components][credentials]' => FALSE,

      'field[settings][minimum_components][title]' => TRUE,
      'field[settings][minimum_components][given]' => FALSE,
      'field[settings][minimum_components][middle]' => FALSE,
      'field[settings][minimum_components][family]' => FALSE,
      'field[settings][minimum_components][generational]' => TRUE,
      'field[settings][minimum_components][credentials]' => TRUE,

      'field[settings][max_length][title]' => 0,
      'field[settings][max_length][given]' => -456,
      'field[settings][max_length][middle]' => 'asdf',
      'field[settings][max_length][family]' => 3454,
      'field[settings][max_length][generational]' => 4.5,
      'field[settings][max_length][credentials]' => 'NULL',

      'field[settings][title_options]' => "-- --\nMr.\nMrs.\nMiss\nMs.\nDr.\nProf.\n[vocabulary:machine]",
      'field[settings][generational_options]' => "-- --\nJr.\nSr.\nI\nII\nIII\nIV\nV\nVI\nVII\nVIII\nIX\nX\n[vocabulary:123]",

    );
    $this->resetAll();
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
      $field_settings, t('Save field settings'));

    $required_messages = array(
      t('!components can not be selected for !label when they are not selected for !label2.',
              array('!label' => t('Minimum components'), '!label2' => t('Components'),
              '!components' => check_plain(implode(', ', array($n['title'], $n['generational'], $n['credentials']))))),

      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['title'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['given'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['middle'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['family'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['generational'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['credentials'])),

      t('!field must have one of the following components: !components', array('!field' => t('Minimum components'), '!components' => check_plain(implode(', ', array($n['given'], $n['family']))))),

      t("The vocabulary 'machine' in !field could not be found.", array('!field' => t('!title options', array('!title' => $n['title'])))),
      t("The vocabulary '123' in !field could not be found.", array('!field' => t('!generational options', array('!generational' => $n['generational'])))),
    );
    foreach ($required_messages as $message) {
      $this->assertText($message);
    }

    // Make sure option lengths do not exceed the title lengths
    $field_settings = array(
      'field[settings][max_length][title]' => 5,
      'field[settings][max_length][generational]' => 3,
      'field[settings][title_options]' => "Aaaaa.\n-- --\nMr.\nMrs.\nBbbbbbbb\nMiss\nMs.\nDr.\nProf.\nCcccc.",
      'field[settings][generational_options]' => "AAAA\n-- --\nJr.\nSr.\nI\nII\nIII\nIV\nV\nVI\nVII\nVIII\nIX\nX\nBBBB",
    );
    $this->resetAll();
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
      $field_settings, t('Save field settings'));
    $required_messages = array(
      t('The following options exceed the maximum allowed !field length: Aaaaa., Bbbbbbbb, Ccccc.', array('!field' => t('!title options', array('!title' => $n['title'])))),
      t('The following options exceed the maximum allowed !field length: AAAA, VIII, BBBB', array('!field' => t('!generational options', array('!generational' => $n['generational'])))),
    );

    foreach ($required_messages as $message) {
      $this->assertText($message);
    }

    // Make sure option have at least one valid option.
    $field_settings = array(
      'field[settings][title_options]' => " \n-- --\n ",
      'field[settings][generational_options]' => " \n-- --\n ",
    );
    $this->resetAll();
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
      $field_settings, t('Save field settings'));
    $required_messages = array(
      t('!field are required.', array('!field' => t('!title options', array('!title' => $n['title'])))),
      t('!field are required.', array('!field' => t('!generational options', array('!generational' => $n['generational'])))),
    );
    foreach ($required_messages as $message) {
      $this->assertText($message);
    }

    // Make sure option have at least one valid only have one default value.
    $field_settings = array(
      'field[settings][title_options]' => "-- --\nMr.\nMrs.\nMiss\n-- Bob\nDr.\nProf.",
      'field[settings][generational_options]' => "-- --\nJr.\nSr.\nI\nII\nIII\nIV\nV\nVI\n--",
    );
    $this->resetAll();
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
      $field_settings, t('Save field settings'));
    $required_messages = array(
      t('!field can only have one blank value assigned to it.', array('!field' => t('!title options', array('!title' => $n['title'])))),
      t('!field can only have one blank value assigned to it.', array('!field' => t('!generational options', array('!generational' => $n['generational'])))),
    );
    foreach ($required_messages as $message) {
      $this->assertText($message);
    }

    // Save the field again with the default values
    $this->resetAll();
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
    $this->name_getFieldSettings(), t('Save field settings'));

    $this->assertText(t('Updated field Test name field settings.'));

    // Now the widget settings...
    // First, check that field validation is working... cut n paste from above test
    $field_settings = array(
      'field[settings][components][title]' => FALSE,
      'field[settings][components][given]' => TRUE,
      'field[settings][components][middle]' => FALSE,
      'field[settings][components][family]' => TRUE,
      'field[settings][components][generational]' => FALSE,
      'field[settings][components][credentials]' => FALSE,

      'field[settings][minimum_components][title]' => TRUE,
      'field[settings][minimum_components][given]' => FALSE,
      'field[settings][minimum_components][middle]' => FALSE,
      'field[settings][minimum_components][family]' => FALSE,
      'field[settings][minimum_components][generational]' => TRUE,
      'field[settings][minimum_components][credentials]' => TRUE,

      'field[settings][max_length][title]' => 0,
      'field[settings][max_length][given]' => -456,
      'field[settings][max_length][middle]' => 'asdf',
      'field[settings][max_length][family]' => 3454,
      'field[settings][max_length][generational]' => 4.5,
      'field[settings][max_length][credentials]' => 'NULL',

      'field[settings][title_options]' => "-- --\nMr.\nMrs.\nMiss\nMs.\nDr.\nProf.\n[vocabulary:machine]",
      'field[settings][generational_options]' => "-- --\nJr.\nSr.\nI\nII\nIII\nIV\nV\nVI\nVII\nVIII\nIX\nX\n[vocabulary:123]",

    );
    $this->resetAll();
    $this->drupalPost('admin/structure/types/manage/page/fields/node.page.field_name_test/field',
      $field_settings, t('Save field settings'));

    $required_messages = array(
      t('!components can not be selected for !label when they are not selected for !label2.',
              array('!label' => t('Minimum components'), '!label2' => t('Components'),
              '!components' => check_plain(implode(', ', array($n['title'], $n['generational'], $n['credentials']))))),

      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['title'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['given'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['middle'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['family'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['generational'])),
      t('!field must be a positive integer between 1 and 255.', array('!field' => $n['credentials'])),

      t('!field must have one of the following components: !components', array('!field' => t('Minimum components'), '!components' => check_plain(implode(', ', array($n['given'], $n['family']))))),

      t("The vocabulary 'machine' in !field could not be found.", array('!field' => t('!title options', array('!title' => $n['title'])))),
      t("The vocabulary '123' in !field could not be found.", array('!field' => t('!generational options', array('!generational' => $n['generational'])))),
    );
    foreach ($required_messages as $message) {
      $this->assertText($message);
    }

    $widget_settings = array(
      'instance[settings][title_display][title]' => 'description', // title, description, none
      'instance[settings][title_display][given]' => 'description',
      'instance[settings][title_display][middle]' => 'description',
      'instance[settings][title_display][family]' => 'description',
      'instance[settings][title_display][generational]' => 'description',
      'instance[settings][title_display][credentials]' => 'description',

      'instance[settings][size][title]' => 6,
      'instance[settings][size][given]' => 20,
      'instance[settings][size][middle]' => 20,
      'instance[settings][size][family]' => 20,
      'instance[settings][size][generational]' => 5,
      'instance[settings][size][credentials]' => 35,

      'instance[settings][inline_css][title]' => '',
      'instance[settings][inline_css][given]' => '',
      'instance[settings][inline_css][middle]' => '',
      'instance[settings][inline_css][family]' => '',
      'instance[settings][inline_css][generational]' => '',
      'instance[settings][inline_css][credentials]' => '',
    );

    $this->resetAll();
    debug(entity_get_bundles('node'));
    $this->drupalGet('admin/structure/types/manage/page/fields/node.page.field_name_test');

    foreach ($widget_settings as $name => $value) {
      $this->assertFieldByName($name, $value);
    }
  }

  function name_getFieldSettings() {
    $field_settings = array(
      'field[settings][components][title]' => TRUE,
      'field[settings][components][given]' => TRUE,
      'field[settings][components][middle]' => TRUE,
      'field[settings][components][family]' => TRUE,
      'field[settings][components][generational]' => TRUE,
      'field[settings][components][credentials]' => TRUE,

      'field[settings][minimum_components][title]' => FALSE,
      'field[settings][minimum_components][given]' => TRUE,
      'field[settings][minimum_components][middle]' => FALSE,
      'field[settings][minimum_components][family]' => TRUE,
      'field[settings][minimum_components][generational]' => FALSE,
      'field[settings][minimum_components][credentials]' => FALSE,

      'field[settings][max_length][title]' => 31,
      'field[settings][max_length][given]' => 63,
      'field[settings][max_length][middle]' => 127,
      'field[settings][max_length][family]' => 63,
      'field[settings][max_length][generational]' => 15,
      'field[settings][max_length][credentials]' => 255,

      'field[settings][labels][title]' => t('Title'),
      'field[settings][labels][given]' => t('Given'),
      'field[settings][labels][middle]' => t('Middle name(s)'),
      'field[settings][labels][family]' => t('Family'),
      'field[settings][labels][generational]' => t('Generational'),
      'field[settings][labels][credentials]' => t('Credentials'),

      'field[settings][sort_options][title]' => TRUE,
      'field[settings][sort_options][generational]' => FALSE,

      'field[settings][title_options]' => "-- --\nMr.\nMrs.\nMiss\nMs.\nDr.\nProf.",
      'field[settings][generational_options]' => "-- --\nJr.\nSr.\nI\nII\nIII\nIV\nV\nVI\nVII\nVIII\nIX\nX",

    );
    return $field_settings;
  }

  function name_getFieldSettingsCheckboxes() {
    $field_settings = array(
      'field[settings][components][title]' => TRUE,
      'field[settings][components][given]' => TRUE,
      'field[settings][components][middle]' => TRUE,
      'field[settings][components][family]' => TRUE,
      'field[settings][components][generational]' => TRUE,
      'field[settings][components][credentials]' => TRUE,

      'field[settings][minimum_components][title]' => FALSE,
      'field[settings][minimum_components][given]' => TRUE,
      'field[settings][minimum_components][middle]' => FALSE,
      'field[settings][minimum_components][family]' => TRUE,
      'field[settings][minimum_components][generational]' => FALSE,
      'field[settings][minimum_components][credentials]' => FALSE,

      'field[settings][sort_options][title]' => TRUE,
      'field[settings][sort_options][generational]' => FALSE,
    );
    return $field_settings;
  }
}

