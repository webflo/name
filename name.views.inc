<?php

/**
 * @file
 * Views integration for name fields.
 */

/**
 * Implements hook_field_views_data().
 *
 * Adds new fulltext name filter handler for every name field.
 */
function name_field_views_data($field) {
  $data = field_views_field_default_views_data($field);

  foreach ($data as $table_name => $table_data) {
    foreach ($table_data as $field_name => $field_data) {
      // @todo: Add fulltext filter.
      /*
      $data[$table_name][$field_name]['filter'] = array(
       'field' => $name,
       'table' => key($name_fields[$name]['storage']['details']['sql'][FIELD_LOAD_CURRENT]),
       'handler' => 'name_handler_filter_name_fulltext',
       'field_name' => $name,
       'allow_empty' => TRUE,
      );
      */
    }
  }

  return $data;
}
