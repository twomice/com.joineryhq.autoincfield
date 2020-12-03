<?php

/**
 * Settings-related utility methods.
 *
 */
class CRM_Autoincfield_Settings {

  public static function getAutoincDetails($id, $cid = NULL) {
    $details = [];
    // Get autoincfield and custom field details
    $autoincfield = \Civi\Api4\Autoincfield::get()
      ->addWhere('custom_field_id', '=', $id)
      ->addChain('name_me_0', \Civi\Api4\CustomField::get()
        ->addWhere('id', '=', '$custom_field_id'),
      0)
      ->execute()
      ->first();

    // Add the details we need
    $details['details'] = $autoincfield['name_me_0'];
    $details['details']['min_value'] = $autoincfield['min_value'];

    // Get the custom group details
    $customGroup = \Civi\Api4\CustomGroup::get()
      ->addWhere('id', '=', $autoincfield['name_me_0']['custom_group_id'])
      ->execute()
      ->first();

    // Add custom group details that we need
    $details['details']['custom_group_title'] = $customGroup['title'];
    $details['details']['table_name'] = $customGroup['table_name'];

    // If contact id exist,
    // get and add the current value of the users autoincfield
    if ($cid) {
      $result = CRM_Core_DAO::executeQuery("SELECT * FROM `{$customGroup['table_name']}` WHERE `entity_id` = {$cid}");
      while ($result->fetch()) {
        $details['details']['autoincval'] = $result->{$autoincfield['name_me_0']['column_name']};
      }
    }

    // Return all the details we need for the edit page
    return $details['details'];
  }

}
