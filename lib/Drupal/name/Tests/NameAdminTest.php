<?php

/**
 * @file
 * Contains \Drupal\name\NameAdminTest.
 *
 * Tests for the name module.
 */

namespace Drupal\name\Tests;

/**
 * Tests for the admin settings and custom format page.
 */
class NameAdminTest extends NameTestHelper {

  public static function getInfo() {
    return array(
      'name' => 'Admin Setting Pages',
      'description' => 'Various tests on the admin area settings.' ,
      'group' => 'Name',
    );
  }

  /**
   * The most basic test. This should only fail if there is a change to the
   * Drupal API.
   */
  function testAdminSettings() {
    global $base_path;

    // Default settings and system settings.
    $this->drupalLogin($this->admin_user);

    // The default installed formats.
    $this->drupalGet('admin/config/regional/name');

    $row_template = array(
      'title href'  => '//tr[@id="name-id"]/td[1]/a/@href',
      'title'       => '//tr[@id="name-id"]/td[1]/a',
      'machine'     => '//tr[@id="name-id"]/td[2]',
      'code'        => '//tr[@id="name-id"]/td[3]',
      'formatted'   => '//tr[@id="name-id"]/td[4]',
      'edit'        => '//tr[@id="name-id"]/td[5]/a',
      'edit link'   => '//tr[@id="name-id"]/td[5]/a/@href',
      'delete'      => '//tr[@id="name-id"]/td[5]/a',
      'delete link' => '//tr[@id="name-id"]/td[5]/a/@href',
    );
    $all_values = array(
      0 => array(
        'title href' => url('admin/config/regional/name/settings'),
        'title' => t('Default'),
        'machine' => 'default',
        'code' => '((((t+ig)+im)+if)+is)+jc',
        'formatted' => 'Mr Joe John Peter Mark Doe Jnr., B.Sc., Ph.D. JOAN SUE DOE Prince ',
      ),
      1 => array(
        'title href' => url('admin/config/regional/name/1'),
        'title' => t('Full'),
        'machine' => 'full',
        'code' => '((((t+ig)+im)+if)+is)+jc',
        'formatted' => 'Mr Joe John Peter Mark Doe Jnr., B.Sc., Ph.D. JOAN SUE DOE Prince ',
        'edit' => t('Edit'),
        'edit link' => url('admin/config/regional/name/1'),
        'delete' => t('Delete'),
        'delete link' => url('admin/config/regional/name/1/delete'),
      ),
      2 => array(
        'title href' => url('admin/config/regional/name/2'),
        'title' => t('Given'),
        'machine' => 'given',
        'code' => 'g',
        'formatted' => 'Joe JOAN Prince ',
        'edit' => t('Edit'),
        'edit link' => url('admin/config/regional/name/2'),
        'delete' => t('Delete'),
        'delete link' => url('admin/config/regional/name/2/delete'),
      ),
      3 => array(
        'title href' => url('admin/config/regional/name/3'),
        'title' => t('Family'),
        'machine' => 'family',
        'code' => 'f',
        'formatted' => 'Doe DOE  ',
        'edit link' => url('admin/config/regional/name/3'),
        'delete link' => url('admin/config/regional/name/3/delete'),
      ),
      4 => array(
        'title href' => url('admin/config/regional/name/4'),
        'title' => t('Title Family'),
        'machine' => 'formal',
        'code' => 't+if',
        'formatted' => 'Mr Doe DOE  ',
        'edit link' => url('admin/config/regional/name/4'),
        'delete link' => url('admin/config/regional/name/4/delete'),
      ),
      5 => array(
        'title href' => url('admin/config/regional/name/5'),
        'title' => t('Given Family'),
        'machine' => 'short_full',
        'code' => 'g+if',
        'formatted' => 'Joe Doe JOAN DOE Prince ',
        'edit link' => url('admin/config/regional/name/5'),
        'delete link' => url('admin/config/regional/name/5/delete'),
      ),
    );

    foreach ($all_values as $id => $row) {
      foreach ($row as $cell_code => $value) {
        $xpath = str_replace('name-id', 'name-'. $id, $row_template[$cell_code]);
        $raw_xpath = $this->xpath($xpath);
        if (!is_array($raw_xpath)) {
          $results = '__MISSING__';
        }
        elseif ($cell_code == 'delete' || $cell_code == 'delete link') {
          $results = $raw_xpath[1];
        }
        else {
          $results = current($raw_xpath);
        }
        $this->assertEqual($results, $value, "Testing {$cell_code} on row {$id} using '{$xpath}' and expecting '". check_plain($value) ."', got '". check_plain($results) ."'.");
      }
    }

    $raw_xpath = $this->xpath('//tr[@id="name-0"]/td[5]/a');
    $results = $raw_xpath ? current($raw_xpath) : '__MISSING__';
    $this->assertEqual($results, t('Edit'), "Testing edit on row 0 using '//tr[@id=\"name-id\"]/td[1]/a' and expecting 'Edit', got '{$results}'.");
    $raw_xpath = $this->xpath('//tr[@id="name-0"]/td[5]/a/@href');
    $results = $raw_xpath ? current($raw_xpath) : '__MISSING__';

    $this->assertEqual($results, url('admin/config/regional/name/settings'), "Testing edit link on row 0 using '//tr[@id=\"name-id\"]/td[1]/a/@href' and expecting url('admin/config/regional/name/settings', got '{$results}'.");


    $this->drupalGet('admin/config/regional/name/settings');

    // Fieldset rendering check
    $this->assertRaw('Format string help', 'Testing the help fieldgroup');

    $default_values = array(
      'name_settings[default_format]' => 't+ig+im+if+is+kc',
      'name_settings[sep1]' => ' ',
      'name_settings[sep2]' => ', ',
      'name_settings[sep3]' => '',
    );
    foreach ($default_values as $name => $value) {
      $this->assertField($name, $value);
    }
    // ID example
    $this->assertFieldById('edit-name-settings-sep1', ' ', t('Sep 3 default value.'));
    $post_values = $default_values;
    $post_values['name_settings[default_format]'] = '';

    $this->drupalPost('admin/config/regional/name/settings', $post_values, t('Save configuration'));
    $this->assertText(t('Default format field is required.'));
    $post_values['name_settings[default_format]'] = '     ';
    $this->drupalPost('admin/config/regional/name/settings', $post_values, t('Save configuration'));
    $this->assertText(t('Default format field is required.'));

    $test_values = array(
      'name_settings[default_format]' => 'c+ks+if+im+ig+t',
      'name_settings[sep1]' => '~',
      'name_settings[sep2]' => '^',
      'name_settings[sep3]' => '-',
    );
    $this->drupalPost('admin/config/regional/name/settings', $test_values, t('Save configuration'));
    $this->assertText(t('The configuration options have been saved.'));

    foreach ($test_values as $name => $value) {
      $this->assertField($name, $value);
    }

    // The default installed formats and the updated default format.
    $this->drupalGet('admin/config/regional/name');

    $xpath = '//tr[@id="name-0"]/td[3]';
    $this->assertEqual(current($this->xpath($xpath)), 'c+ks+if+im+ig+t', 'Default is equal to set default.');

    $this->drupalGet('admin/config/regional/name/add');
    $this->assertRaw('Format string help', 'Testing the help fieldgroup');
    $values = array('name' => '', 'machine_name' => '', 'format' => '');
    $this->drupalPost('admin/config/regional/name/add', $values, t('Save'));
    foreach (array(t('Name'), t('Machine-readable name'), t('Format')) as $title) {
      $this->assertText(t('!field field is required', array('!field' => $title)));
    }
    $values = array('name' => 'given', 'machine_name' => '1234567890abcdefghijklmnopqrstuvwxyz_', 'format' => 'a');
    $this->drupalPost('admin/config/regional/name/add', $values, t('Save'));
    $this->assertText(t('The name you have chosen is already in use.'));
    $this->assertNoText(t('!field field is required', array('!field' => t('Format'))));
    $this->assertNoText(t('!field field is required', array('!field' => t('Machine-readable name'))));

    $values = array('name' => 'given', 'machine_name' => '%&*(', 'format' => 'a');
    $this->drupalPost('admin/config/regional/name/add', $values, t('Save'));
    $this->assertText(t('The machine-readable name must contain only lowercase letters, numbers, and underscores.'));

    $values = array('name' => 'given', 'machine_name' => 'given', 'format' => 'a');
    $this->drupalPost('admin/config/regional/name/add', $values, t('Save'));
    $this->assertText(t('The machine-readable name you have chosen is already in use.'));

    $values = array('name' => 'given', 'machine_name' => 'default', 'format' => 'a');
    $this->drupalPost('admin/config/regional/name/add', $values, t('Save'));
    $this->assertText(t('The machine-readable name you have chosen is reserved.'));

    $values = array('name' => 'Test', 'machine_name' => 'test', 'format' => 'abc');
    $this->drupalPost('admin/config/regional/name/add', $values, t('Save'));
    $this->assertText(t('Custom format Test has been created.'));

    $new_rows = array(
      6 => array(
        'title href' => url('admin/config/regional/name/6'),
        'title' => 'Test',
        'machine' => 'test',
        'code' => 'abc',
        'formatted' => 'abB.Sc., Ph.D. ab ab ',
        'edit link' => url('admin/config/regional/name/6'),
        'delete link' => url('admin/config/regional/name/6/delete'),
      ),
    );

    foreach ($new_rows as $id => $row) {
      foreach ($row as $cell_code => $value) {
        $xpath = str_replace('name-id', 'name-'. $id, $row_template[$cell_code]);
        $raw_xpath = $this->xpath($xpath);
        if (!is_array($raw_xpath)) {
          $results = '__MISSING__';
        }
        elseif ($cell_code == 'delete' || $cell_code == 'delete link') {
          $results = count($raw_xpath) > 1 ? $raw_xpath[1] : '__MISSING__';
        }
        else {
          $results = current($raw_xpath);
        }
        $this->assertEqual($results, $value, "Testing {$cell_code} on row {$id} using '{$xpath}' and expecting '{$value}', got '{$results}'.");
      }
    }
    $values = array('name' => 'new name', 'machine_name' => 'bob', 'format' => 'f+g');
    $this->drupalPost('admin/config/regional/name/6', $values, t('Save'));
    $this->assertText(t('Custom format new name has been updated.'));
    $new_rows = array(
      6 => array(
        'title' => $values['name'],
        'machine' => $values['machine_name'],
        'code' => $values['format'],
      ),
    );
    foreach ($new_rows as $id => $row) {
      foreach ($row as $cell_code => $value) {
        $xpath = str_replace('name-id', 'name-'. $id, $row_template[$cell_code]);
        $raw_xpath = $this->xpath($xpath);
        if (!is_array($raw_xpath)) {
          $results = '__MISSING__';
        }
        elseif ($cell_code == 'delete' || $cell_code == 'delete link') {
          $results = count($raw_xpath) > 1 ? $raw_xpath[1] : '__MISSING__';
        }
        else {
          $results = current($raw_xpath);
        }
        $this->assertEqual($results, $value, "Testing {$cell_code} on row {$id} using '{$xpath}' and expecting '{$value}', got '{$results}'.");
      }
    }

    $this->drupalGet('admin/config/regional/name/60');
    $this->assertText(t('The custom format could not be found.'));
    $this->drupalGet('admin/config/regional/name/60/delete');
    $this->assertText(t('The custom format could not be found.'));

    $this->drupalPost('admin/config/regional/name/4', array(), t('Delete'));
    $this->assertText(t('Are you sure you want to delete the custom format !title ("t+if")?', array('!title' => check_plain(t('Title Family')))));

    $this->drupalPost(NULL, array('confirm' => 1), t('Delete'));
    $this->assertText(t('The custom name format !title was deleted.', array('!title' => check_plain('Title Family'))));

  }
}
