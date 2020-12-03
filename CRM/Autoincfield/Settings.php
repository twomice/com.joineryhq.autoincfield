<?php

/**
 * Settings-related utility methods.
 *
 */
class CRM_Autoincfield_Settings {

  public static function getAutoincDetails($id, $cid = NULL) {
    $details = [];
    $autoincfield = \Civi\Api4\Autoincfield::get()
      ->addWhere('custom_field_id', '=', $id)
      ->addChain('name_me_0', \Civi\Api4\CustomField::get()
        ->addWhere('id', '=', '$custom_field_id'),
      0)
      ->execute()
      ->first();

    // Add Custom field details
    $details['details'] = $autoincfield['name_me_0'];
    $details['details']['min_value'] = $autoincfield['min_value'];

    $customGroup = \Civi\Api4\CustomGroup::get()
      ->addWhere('id', '=', $autoincfield['name_me_0']['custom_group_id'])
      ->execute()
      ->first();

    // Add Custom Group details that we need
    $details['details']['custom_group_title'] = $customGroup['title'];
    $details['details']['table_name'] = $customGroup['table_name'];

    // Get and add the current value of the users autoincfield
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
