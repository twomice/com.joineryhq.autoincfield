<?php

require_once 'autoincfield.civix.php';
// phpcs:disable
use CRM_Autoincfield_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission
 */
function autoincfield_civicrm_permission(&$permissions) {
  // name of extension or module
  $prefix = E::ts('CiviCRM Autoincfield') . ': ';
  $permissions['access Autoincfield'] = array(
    // label
    $prefix . E::ts('access Autoincfield'),
    // description
    E::ts('Edit CiviCRM Autoincfield value of the user'),
  );
}

/**
 * Implements hook_civicrm_pageRun().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pageRun
 */
function autoincfield_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Custom_Page_Field') {
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.autoincfield', 'js/CRM_Custom_Page_Field.js', 100, 'page-footer');
  }

  // Add edit button in contact page summary if autoincfield exist and permission matches
  if ($pageName == 'CRM_Contact_Page_View_Summary' && CRM_Core_Permission::check('access Autoincfield')) {
    $contactID['contactID'] = CRM_Utils_Request::retrieve('cid', 'Positive');

    CRM_Core_Resources::singleton()->addVars('autoincfield', $contactID);
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.autoincfield', 'js/CRM_Contact_Page_View_Summary.js', 100, 'page-footer');
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function autoincfield_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Custom_Form_Field') {
    if ($form->elementExists('data_type')) {
      // Add autoincfield js
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.autoincfield', 'js/CRM_Custom_Form_Field.js', 100, 'page-footer');

      // Create necessary fields
      $form->addElement('checkbox', 'autoinc', E::ts('Is Autoincrement?'));
      $form->addElement('text', 'min_value', E::ts('Minimum next value'));
      // Assign bhfe fields to the template.
      $tpl = CRM_Core_Smarty::singleton();
      $bhfe = $tpl->get_template_vars('beginHookFormElements');
      if (!$bhfe) {
        $bhfe = array();
      }
      $bhfe[] = 'autoinc';
      $bhfe[] = 'min_value';
      $form->assign('beginHookFormElements', $bhfe);

      // Set default values if update page
      if (!empty($form->_defaultValues['id'])) {
        $getAutoincfield = \Civi\Api4\Autoincfield::get()
          ->addWhere('custom_field.id', '=', $form->_defaultValues['id'])
          ->execute();

        $defaults = array();

        if (!empty($getAutoincfield[0]['custom_field_id'])) {
          $defaults['autoinc'] = 1;
        }

        if (!empty($getAutoincfield[0]['min_value'])) {
          $defaults['min_value'] = $getAutoincfield[0]['min_value'];
        }

        $form->setDefaults($defaults);
      }
    }
  }
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_validateForm/
 */
function autoincfield_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Custom_Form_Field' && $fields['autoinc']) {
    $fieldMinVal = $fields['min_value'];

    if (!empty($fieldMinVal)) {
      if ($fieldMinVal < 0) {
        $errors['min_value'] = E::ts('Minimum next value field should not be below zero.');
        return;
      }

      if (!is_numeric($fieldMinVal)) {
        $errors['min_value'] = E::ts('Minimum next value field should only have numeric value.');
        return;
      }

      if ($form->getVar('_id')) {
        $customFieldID = $form->getVar('_id');
        $autoincfield = \Civi\Api4\Autoincfield::get()
          ->addWhere('custom_field_id', '=', $customFieldID)
          ->execute();

        if (!empty($autoincfield[0]['min_value']) && $fieldMinVal != $autoincfield[0]['min_value']) {
          $query = "SELECT * FROM `civicrm_autoincfield_$customFieldID` ORDER BY `counter` DESC";
          $customAutoincfield = CRM_Core_DAO::singleValueQuery($query);
          $counterVal = $customAutoincfield;

          if ($counterVal < $autoincfield[0]['min_value']) {
            $counterVal = $counterVal + 1;
          }

          if ($fieldMinVal <= $counterVal) {
            $errors['min_value'] = E::ts("Minimum next value field should not be below or equal to {$counterVal}.");
            return;
          }
        }
      }

      // Check table if not exist yet
      if (!CRM_Core_DAO::checkTableExists('civicrm_autoincfield_' . $form->getVar('_id'))) {
        $lastValue = _autoincfield_civicrm_lastValue($form->getVar('_id'));

        if (!empty($lastValue) && $lastValue >= $fieldMinVal) {
          $errors['min_value'] = E::ts("Since this custom field has existing values. Minimum next value field should not be below or equal to this custom fields last value which is {$lastValue}.");
          return;
        }
      }
    }
  }

  return;
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess
 */
function autoincfield_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Custom_Form_Field' && $form->_submitValues['autoinc']) {
    $values = $form->_submitValues;
    // Get the id of the latest custom field
    $latestCustomField = \Civi\Api4\CustomField::get()
      ->addWhere('id', '=', $form->getVar('_id'))
      ->execute();

    $customFieldID = $latestCustomField[0]['id'];
    $minVal = 0;

    if (!empty($form->_submitValues['min_value'])) {
      $minVal = $form->_submitValues['min_value'];
    }

    // Check table if not exist yet
    if (!CRM_Core_DAO::checkTableExists('civicrm_autoincfield_' . $customFieldID)) {
      $lastValue = _autoincfield_civicrm_lastValue($form->getVar('_id'));
      if (!empty($lastValue)) {
        $minVal = $lastValue + 1;
      }

      // Save in Autoincfield database
      $createAutoincfield = \Civi\Api4\Autoincfield::create()
        ->addValue('custom_field_id', $customFieldID)
        ->addValue('min_value', $minVal)
        ->execute();

      // Create table for customfield
      $table = array(
        'name' => 'civicrm_autoincfield_' . $customFieldID,
        'attributes' => '',
        'fields' => array(
          array(
            'name' => 'counter',
            // TODO: remove UNSIGNED here, in order to support negative values:
            'type' => 'INT AUTO_INCREMENT',
            'primary' => TRUE,
            'required' => TRUE,
            'comment' => 'Primary key',
          ),
          array(
            'name' => 'timestamp',
            'type' => 'TIMESTAMP',
            'required' => TRUE,
            'comment' => 'Timestamp',
          ),
        ),
      );

      CRM_Core_BAO_SchemaHandler::createTable($table);

      // TODO: insert a row in $table with counter=($min_value -1);
      // Example code:
      $counterVal = ($minVal - 1);
      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_autoincfield_{$customFieldID} (`counter`, `timestamp`) VALUES ('$counterVal', NOW())");

      // Add unique to the autoincfield column in the civicrm_value_N table
      $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($customFieldID);
      CRM_Core_DAO::executeQuery("ALTER TABLE `{$autoincdetails['table_name']}` ADD UNIQUE `{$autoincdetails['column_name']}` (`{$autoincdetails['column_name']}`)");
    }
    else {

      $autoincfield = \Civi\Api4\Autoincfield::get()
        ->addWhere('custom_field_id', '=', $customFieldID)
        ->execute();

      if ($minVal != $autoincfield[0]['min_value']) {
        // If table exist, its the update page, update min_value only
        $updateResults = \Civi\Api4\Autoincfield::update()
          ->addWhere('custom_field_id', '=', $customFieldID)
          ->addValue('min_value', $minVal)
          ->execute();

        $counterVal = ($minVal - 1);
        CRM_Core_DAO::executeQuery("INSERT INTO civicrm_autoincfield_{$customFieldID} (`counter`, `timestamp`) VALUES ('$counterVal', NOW())");
      }
    }
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
 */
function autoincfield_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // If new entity is created
  if ($op == 'create') {
    // Set subTypes and subName as NULL default for the getTree function
    // https://doc.symbiotic.coop/dev/civicrm/v47/phpdoc/CRM_Core_BAO_CustomGroup.html#method_getTree
    $subTypes = NULL;
    $subName = NULL;

    // Update subTypes value if $objectName match the case
    switch ($objectName) {
      case 'Activity':
        $subTypes = $objectRef->activity_type_id;
        break;

      case 'Campaign':
        $subTypes = $objectRef->campaign_type_id;
        break;

      case 'Contribution':
        $subTypes = $objectRef->financial_type_id;
        break;

      case 'Event':
        $subTypes = $objectRef->event_type_id;
        break;

      case 'Grant':
        $subTypes = $objectRef->grant_type_id;
        break;

      case 'Individual':
        $subTypes = $objectRef->contact_sub_type;
        break;

      case 'Membership':
        $subTypes = $objectRef->membership_type_id;
        break;

      case 'Organization':
        $subTypes = $objectRef->contact_sub_type;
        break;

      case 'Relationship':
        $subTypes = $objectRef->relationship_type_id;
        break;

      case 'Participant':
        $subTypes = $objectRef->event_id;

        // Update subTypes and $subName value to match on Participant $objectname
        // Check CRM\Event\Form\ParticipantView.php line 136
        // ParticipantEventType is not working yet
        $fields = CRM_Core_BAO_CustomField::getFields($objectName);
        foreach ($fields as $field) {
          if ($field['extends_entity_column_id'] == 1) {
            $customDataType = CRM_Core_OptionGroup::values('custom_data_type', FALSE, FALSE, FALSE, NULL, 'name');
            $roleCustomDataTypeID = array_search('ParticipantRole', $customDataType);
            $subTypes = $objectRef->role_id;
            $subName = $roleCustomDataTypeID;
          }
          elseif ($field['extends_entity_column_id'] == 3) {
            $customDataType = CRM_Core_OptionGroup::values('custom_data_type', FALSE, FALSE, FALSE, NULL, 'name');
            $eventTypeID = CRM_Core_DAO::getFieldValue("CRM_Event_DAO_Event", $objectRef->event_id, 'event_type_id', 'id');
            $eventTypeCustomDataTypeID = array_search('ParticipantEventType', $customDataType);
            $subTypes = $eventTypeID;
            $subName = $eventTypeCustomDataTypeID;
          }
        }
        break;

      default:
        return;
    }

    if ($subTypes === 'null') {
      $subTypes = NULL;
    }

    // Set getTree function
    $getTreeResults = CRM_Core_BAO_CustomGroup::getTree(
      //  $entityType Of the contact whose contact type is needed.
      $objectName,
      //  $toReturn What data should be returned. ['custom_group' => ['id', 'name', etc.], 'custom_field' => ['id', 'label', etc.]]
      NULL,
      //  $entityID
      $objectId,
      //  $groupID
      -1,
      //  $subTypes
      $subTypes,
      //  $subName
      $subName,
      //  $fromCache
      NULL,
      //  $onlySubType Only return specified subtype or return specified subtype + unrestricted fields.
      NULL,
      //  $returnAll Do not restrict by subtype at all.
      TRUE,
      //  $checkPermission
      NULL,
      //  $singleRecord holds 'new' or id if view/edit/copy form for a single record is being loaded.
      NULL,
      //  $showPublicOnly
      NULL
    );

    // Get all data on autoincfield table
    $getAutoincfieldData = \Civi\Api4\Autoincfield::get()->execute();
    $autoinc = [];

    // Sort data to match on getTreeResults
    foreach ($getAutoincfieldData as $autoincfield) {
      $autoinc[$autoincfield['custom_field_id']]['id'] = $autoincfield['custom_field_id'];
      $autoinc[$autoincfield['custom_field_id']]['min_value'] = $autoincfield['min_value'];
    }

    foreach ($getTreeResults as $getTreeFields) {
      if (!empty($getTreeFields['fields'])) {
        // Get custom group name for updating the values
        $customGroupName = $getTreeFields['name'];

        foreach ($getTreeFields['fields'] as $field) {
          $customFieldName = $field['name'];
          $customFieldTable = "{$customGroupName}.{$customFieldName}";
          // If field id is not empty and it match on the autoincfield data
          if (!empty($autoinc[$field['id']]) && in_array($field['id'], $autoinc[$field['id']])) {
            // If custom group has extends_entity_column_value, check if it matches the subTypes to save
            if (!empty($getTreeFields['extends_entity_column_value'])) {
              if ($getTreeFields['extends_entity_column_value'] == $subTypes) {
                _autoincfield_autoincfieldDataUpdate($field['id'], $objectName, $objectId, $customFieldTable);
              }
            }
            else {
              _autoincfield_autoincfieldDataUpdate($field['id'], $objectName, $objectId, $customFieldTable);
            }
          }
        }
      }
    }
  }

  // Drop custom table if custom field is deleted
  if ($op == 'delete' && $objectName == 'CustomField') {
    $sqlDrop = "DROP TABLE IF EXISTS `civicrm_autoincfield_$objectId`";
    CRM_Core_DAO::executeQuery($sqlDrop);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function autoincfield_civicrm_config(&$config) {
  _autoincfield_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function autoincfield_civicrm_xmlMenu(&$files) {
  _autoincfield_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function autoincfield_civicrm_install() {
  _autoincfield_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function autoincfield_civicrm_postInstall() {
  _autoincfield_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function autoincfield_civicrm_uninstall() {
  _autoincfield_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function autoincfield_civicrm_enable() {
  _autoincfield_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function autoincfield_civicrm_disable() {
  _autoincfield_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function autoincfield_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _autoincfield_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function autoincfield_civicrm_managed(&$entities) {
  _autoincfield_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function autoincfield_civicrm_caseTypes(&$caseTypes) {
  _autoincfield_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function autoincfield_civicrm_angularModules(&$angularModules) {
  _autoincfield_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function autoincfield_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _autoincfield_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function autoincfield_civicrm_entityTypes(&$entityTypes) {
  _autoincfield_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function autoincfield_civicrm_themes(&$themes) {
  _autoincfield_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function autoincfield_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function autoincfield_civicrm_navigationMenu(&$menu) {
//  _autoincfield_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _autoincfield_civix_navigationMenu($menu);
//}

/**
 * Update autoincfield custom table, update custom field entity value...
 * and delete autoincfield custom table data in portProcess hook
 *
 * @param Int $fieldID, String $objectName, Int $objectId, String $customFieldTable
 *
 */
function _autoincfield_autoincfieldDataUpdate($fieldID, $objectName, $objectId, $customFieldTable) {
  $autoincValue = _autoincfield_get_nextAutoincValue($fieldID);
  if ($autoincValue == NULL) {
    // Autoincrement value could not be determined. Log an error and do
    // nothing more on this field.
    Civi::log()->error('AUTOINCFIELD: could not determined next autoincrement value for custom field ' . $fieldID);
  }

  $apiEntityName = $objectName;
  if (
    $objectName == 'Individual'
    || $objectName == 'Organization'
    || $objectName == 'Household'
  ) {
    $apiEntityName = 'Contact';
  }

  $results = civicrm_api4($apiEntityName, 'update', [
    'where' => [
      ['id', '=', $objectId],
    ],
    'values' => [
      $customFieldTable => $autoincValue,
    ],
  ]);

  // Delete data that's more than 24 hours
  $sqlDeleteData = "DELETE FROM `civicrm_autoincfield_$fieldID` WHERE `timestamp` <= DATE_SUB(NOW(), INTERVAL 1 DAY)";
  CRM_Core_DAO::executeQuery($sqlDeleteData);
}

/**
 * For a given autoincfield, determine the appropriate next autoincrement value.
 *
 * @param Int $fieldID
 *
 * @return Int|NULL
 *   Integer next value, or NULL if that can't be determined.
 */
function _autoincfield_get_nextAutoincValue($fieldID) {
  // Save to the database autoincfield custom table
  $sql = "INSERT INTO `civicrm_autoincfield_$fieldID` (`counter`,`timestamp`) VALUES (NULL, NOW())";
  CRM_Core_DAO::executeQuery($sql);

  // Add value to autoincrement field in each user Contact, Participant, Contribution, Event etc...
  $query = "SELECT LAST_INSERT_ID();";
  $lastID = CRM_Core_DAO::singleValueQuery($query);

  return $lastID;
}

/**
 * Get the last value of an existing integer custom field.
 *
 * @param Int $customFieldId
 *
 * @return Int|NULL
 *   Custom field last value, or NULL if that can't be determined.
 */
function _autoincfield_civicrm_lastValue($customFieldId) {
  $customFields = \Civi\Api4\CustomField::get()
    ->addWhere('id', '=', $customFieldId)
    ->addChain('name_me_0', \Civi\Api4\CustomGroup::get()
      ->addWhere('id', '=', '$custom_group_id'),
    0)
    ->execute();

  $lastValue = NULL;

  foreach ($customFields as $customField) {
    $customFieldName = $customField['name'];
    $customGroupName = $customField['name_me_0']['name'];
    $customGroupEntity = $customField['name_me_0']['extends'];

    $apiEntityName = $customGroupEntity;
    if (
      $customGroupEntity == 'Individual'
      || $customGroupEntity == 'Organization'
      || $customGroupEntity == 'Household'
    ) {
      $apiEntityName = 'Contact';
    }

    $apiResults = civicrm_api4($apiEntityName, 'get', [
      'select' => [
        "{$customGroupName}.{$customFieldName}",
      ],
      'orderBy' => [
        "{$customGroupName}.{$customFieldName}" => 'DESC',
      ],
      'limit' => 1,
    ]);

    $lastValue = $apiResults[0]["{$customGroupName}.{$customFieldName}"];
  }

  return $lastValue;
}
