<?php
$xpdo_meta_map['usersSynchUsersTemp']= array (
  'package' => 'userssynch',
  'version' => '1.1',
  'table' => 'userssynch_users_temp',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'fullname' => '',
    'uuid' => '',
    'phone' => '',
    'inn' => NULL,
    'form_type' => 'juridical',
    'price_group' => 0,
    'subscribe' => 1,
  ),
  'fieldMeta' => 
  array (
    'fullname' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'uuid' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '74',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'phone' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'inn' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'form_type' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => true,
      'default' => 'juridical',
    ),
    'price_group' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'subscribe' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 1,
    ),
  ),
  'indexes' => 
  array (
    'uuid' => 
    array (
      'alias' => 'uuid',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'uuid' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
