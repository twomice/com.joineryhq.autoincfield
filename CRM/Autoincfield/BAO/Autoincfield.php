<?php
use CRM_Autoincfield_ExtensionUtil as E;

class CRM_Autoincfield_BAO_Autoincfield extends CRM_Autoincfield_DAO_Autoincfield {

  /**
   * Create a new Autoincfield based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Autoincfield_DAO_Autoincfield|NULL
   *
  public static function create($params) {
    $className = 'CRM_Autoincfield_DAO_Autoincfield';
    $entityName = 'Autoincfield';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
