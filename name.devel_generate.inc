<?php

/**
 * Devel_generate callback
 */
function name_devel_generate($object, $field, $instance, $bundle) {
  if (field_behaviors_widget('multiple values', $instance) == FIELD_BEHAVIOR_CUSTOM) {
    return devel_generate_multiple('_name_devel_generate', $object, $field, $instance, $bundle);
  }
  else {
    return _name_devel_generate($object, $field, $instance, $bundle);
  }
}

/**
 * Helper function to generate name instanse of field data
 */
function _name_devel_generate($object, $field, $instance, $bundle) {
  $object_field = array();

  $func = variable_get('name_devel_generate_names', '_name_devel_generate_name');
  $names = $func($field['settings']);
  
  $components = array_filter($field['settings']['components']);
  $rand_index  = array_rand($names);

  return array_flip(array_intersect(array_flip($names[$rand_index]), $components));
}

/**
 * Helper function to generate set of fake names
 *
 * @param settings
 *   a settings array of a name field instance
 */
function _name_devel_generate_name($settings) {
  $names = array();

  // Prepare data
  $titles = explode("\r\n", $settings['title_options']);
  $generational = explode("\r\n", $settings['generational_options']);
  for ($i = 0; $i < 50; $i++) {
    $credentials[] = ucfirst(devel_generate_word(rand(1, 10)));
  }

  $data = array(
    'title' => $titles,
    'given' => array('John', 'William', 'James', 'George', 'Charles', 'Frank', 'Joseph', 'Henry', 'Robert', 'Thomas', 'Edward', 'Harry', 'Walter', 'Arthur', 'Fred', 'Albert', 'Samuel', 'Clarence', 'Louis', 'David', 'Joe', 'Charlie', 'Richard', 'Ernest', 'Roy', 'Will', 'Andrew', 'Jesse', 'Oscar', 'Willie', 'Daniel', 'Benjamin', 'Carl', 'Sam', 'Alfred', 'Earl', 'Peter', 'Elmer', 'Frederick', 'Howard', 'Lewis', 'Ralph', 'Herbert', 'Paul', 'Lee', 'Tom', 'Herman', 'Martin', 'Jacob', 'Michael', 'Mary', 'Anna', 'Emma', 'Elizabeth', 'Margaret', 'Minnie', 'Ida', 'Bertha', 'Clara', 'Alice', 'Annie', 'Florence', 'Bessie', 'Grace', 'Ethel', 'Sarah', 'Ella', 'Martha', 'Nellie', 'Mabel', 'Laura', 'Carrie', 'Cora', 'Helen', 'Maude', 'Lillian', 'Gertrude', 'Rose', 'Edna', 'Pearl', 'Edith', 'Jennie', 'Hattie', 'Mattie', 'Eva', 'Julia', 'Myrtle', 'Louise', 'Lillie', 'Jessie', 'Frances', 'Catherine', 'Lula', 'Lena', 'Marie', 'Ada', 'Josephine', 'Fanny', 'Lucy', 'Dora'),
    'middle' => array('Aaron', 'Bailey', 'Carson', 'Damon', 'Edwin', 'Francis', 'Garrett', 'Holden', 'Ivan', 'Jace', 'Keaton', 'Layne', 'Malcolm', 'Noah', 'Owen', 'Payton', 'Quinn', 'Randall', 'Sawyer', 'Tilton', 'Tanner', 'Vernon', 'Wade', 'Zachariah', 'Aiden', 'Bennett', 'Chance', 'Dante', 'Ellis', 'Glenn', 'Houston', 'Jackson', 'Kelton', 'Layton', 'Marshall', 'Noel', 'Peyton', 'Quintin', 'Reese', 'Sean', 'Stewart', 'Taylor', 'Warren', 'Anton', 'Blair', 'Charles', 'Denver', 'Emmett', 'Grant', 'Jade', 'Adele', 'Bailee', 'Camden', 'Dawn', 'Elein', 'Fawn', 'Haiden', 'Jacklyn', 'Kae', 'Lane', 'Madisen', 'Nadeen', 'Ocean', 'Payten', 'Raine', 'Selene', 'Taye', 'Zion', 'Alice', 'Berlynn', 'Candice', 'Debree', 'Ellen', 'Faye', 'Hollyn', 'Jae', 'Kaitlin', 'Lashon', 'Mae', 'Naveen', 'Raven', 'Sharon', 'Taylore', 'Zoe', 'Anise', 'Bernice', 'Carelyn', 'Debree', 'Erin', 'Faye', 'Hollyn', 'Jane', 'Kalan', 'Lee', 'Merle', 'Olive', 'Reagan', 'Sue', 'Ann', 'Bree'),
    'family' => array('Smith', 'Johnson', 'Williams', 'James', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin', 'Tompson', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodrigez', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'Hernandez', 'King', 'Wright', 'Lopez', 'Hill', 'Scott', 'Green', 'Adams', 'Baker', 'Gonzales', 'Nelson', 'Carter', 'Mitchell', 'Perez', 'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker', 'Evans', 'Edwards', 'Collins', 'Stewart', 'Sanches', 'Morris', 'Rogers', 'Reed', 'Cook', 'Morgan', 'Bell', 'Murphy', 'Bailey', 'Rivera', 'Cooper', 'Richardson', 'Cox', 'Howard', 'Ward', 'Torez', 'Peterson', 'Gray', 'Ramirez', 'James', 'Watson', 'Brooks', 'Kelly', 'Sanders', 'Price', 'Bennett', 'Wood', 'Barness', 'Ross', 'Henderson', 'Coleman', 'Jenkins', 'Perry', 'Powel', 'Long', 'Patterson', 'Hughes', 'Flores', 'Washington', 'Butler', 'Simpson', 'Foster', 'Gonzales', 'Bryant', 'Alexander', 'Russel', 'Griffin', 'Diaz', 'Hayes'),
    'generational' => $generational,
    'credentials' => $credentials,
  );

  // Generate list of random names
  for ($i = 0; $i < 10; $i++) {
    $names[] = array(
      'title' => $data['title'][array_rand($data['title'])],
      'given' => $data['given'][array_rand($data['given'])],
      'middle' => $data['middle'][array_rand($data['middle'])],
      'family' => $data['family'][array_rand($data['family'])],
      'generational' => $data['generational'][array_rand($data['generational'])],
      'credentials' => $data['credentials'][array_rand($data['credentials'])],
    );
  }

  return $names;
}