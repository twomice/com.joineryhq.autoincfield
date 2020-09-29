<?php

require_once 'autoincfield.civix.php';
// phpcs:disable
use CRM_Autoincfield_ExtensionUtil as E;
// phpcs:enable

function autoincfield_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Custom_Page_Field') {
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.autoincfield', 'js/autoincfield-CRM-Custom-Page-Field.js', 100, 'page-footer');
  }

  // $result = CRM_Core_DAO::executeQuery('SELECT custom_field_id FROM civicrm_autoincfield');
  // while ($result->fetch()) {
  //   echo "<pre>";
  //   print_r($result->custom_field_id);
  //   echo "</pre>";
  // }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function autoincfield_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Custom_Form_Field') {
    if ($form->elementExists('data_type')) {
      $dataTypes = $form->getElement('data_type');

      // Inject autoincrement in datatypes with the integer value
      $autoIncArr = array(
        'text' => 'Autoincrement',
        'attr' => array(
          'value' => 1,
        ),
      );
      array_push($dataTypes->_elements[0]->_options, $autoIncArr);

      // Add autoincfield js
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.autoincfield', 'js/autoincfield.js', 100, 'page-footer');

      // Create necessary fields
      $form->addElement('hidden', 'autoinc');
      $form->addElement('text', 'min_value', ts('Minimum next value'));
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
            'type' => 'INT UNSIGNED AUTO_INCREMENT',
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
    }
    // else {
    //   // If table exist, its the update page, update min_value only
    //   $results = \Civi\Api4\Autoincfield::update()
    //   ->addWhere('custom_field_id', '=', $customFieldID)
    //   ->addValue('min_value', $minVal)
    //   ->execute();
    // }
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
    }

    // Set getTree function
    $getTreeResults = CRM_Core_BAO_CustomGroup::getTree(
      $objectName,
      NULL,
      $objectId,
      -1,
      $subTypes,
      $subName,
      NULL,
      NULL,
      TRUE
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
        foreach ($getTreeFields['fields'] as $field) {
          // If field id is not empty and it match on the autoincfield data
          if (!empty($autoinc[$field['id']]) && in_array($field['id'], $autoinc[$field['id']])) {
            $fieldID = $field['id'];
            $timestamp = date('Y-m-d H:i:s');
            $autoincValue = NULL;

            // Check if there is a data on the counter column
            // Update autoincValue to the min_value if there is no data on the counter column
            $customAutoincValue = CRM_Core_DAO::checkFieldHasAlwaysValue("civicrm_autoincfield_$fieldID", 'counter', '');
            if ($customAutoincValue) {
              $autoincValue = $autoinc[$fieldID]['min_value'];
            }

            // Save to the database custom table
            $sql = "INSERT INTO `civicrm_autoincfield_$fieldID` (`counter`,`timestamp`) VALUES ('$autoincValue', '$timestamp')";
            CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);

            // Delete data that's more than 24 hours
            $sqlDeleteData = "DELETE FROM `civicrm_autoincfield_$fieldID` WHERE `timestamp` <= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            CRM_Core_DAO::executeQuery($sqlDeleteData);
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
