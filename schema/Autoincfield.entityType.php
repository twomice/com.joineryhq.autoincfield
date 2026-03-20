<?php
use CRM_Autoincfield_ExtensionUtil as E;

return [
  'name' => 'Autoincfield',
  'table' => 'civicrm_autoincfield',
  'class' => 'CRM_Autoincfield_DAO_Autoincfield',
  'getInfo' => fn() => [
    'title' => E::ts('Autoincfield'),
    'title_plural' => E::ts('Autoincfields'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique Autoincfield ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'custom_field_id' => [
      'title' => E::ts('Custom Field ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Custom Field ID'),
      'entity_reference' => [
        'entity' => 'CustomField',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'min_value' => [
      'title' => E::ts('Min Value'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'description' => E::ts('Integer, the minimum value upon next usage'),
    ],
  ],
];
