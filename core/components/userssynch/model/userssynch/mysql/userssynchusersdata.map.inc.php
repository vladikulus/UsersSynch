<?php
$xpdo_meta_map['usersSynchUsersData']= array (
  'package' => 'userssynch',
  'version' => '1.1',
  'table' => 'userssynch_users',
  'extends' => 'xPDOObject',
  'fields' => 
  array (
    'user_id' => 0,
    'uuid_1c' => '',
    'uuid' => '',
  ),
  'fieldMeta' => 
  array (
    'user_id' => 
    array (
      'dbtype' => 'integer',
      'attributes' => 'unsigned',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'pk',
    ),
    'uuid_1c' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '74',
      'phptype' => 'string',
      'null' => true,
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
