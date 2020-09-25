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

  // $fields = CRM_Core_BAO_CustomField::getFields('Participant');

  // $result = civicrm_api3('CustomGroup', 'get', [
  //   'sequential' => 1,
  //   'id' => 3,
  //   'return' => ["extends"],
  // ]);
  // $int = (int) filter_var($fields[4]['extends_entity_column_value'], FILTER_SANITIZE_NUMBER_INT);

  // echo "<pre>";
  // print_r($eventTypeCustomDataTypeID);
  // echo "</pre>";
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function autoincfield_civicrm_buildForm($formName, &$form) {
  if($formName == 'CRM_Custom_Form_Field') {
    if ( $form->elementExists( 'data_type' ) ) {
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
        $getAutoincfield = civicrm_api3('Autoincfield', 'get', [
          'custom_field_id' => $form->_defaultValues['id'],
        ]);
        $defaults = array();

        $currentAutoincfield = array_values($getAutoincfield);

        if (!empty($currentAutoincfield[0]['custom_field_id'])) {
          $defaults['autoinc'] = 1;
        }

        if (!empty($currentAutoincfield[0]['min_value'])) {
          $defaults['min_value'] = $currentAutoincfield[0]['min_value'];
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
    $latestCustomField = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'id' => $form->getVar('_id'),
    ]);

    $minVal = 0;
    $args = [
      'custom_field_id' => $latestCustomField['id'],
      'min_value' => $minVal,
    ];

    if (!empty($form->_submitValues['min_value'])) {
      $args['min_value'] = $form->_submitValues['min_value'];
    }

    // Save in Autoincfield database
    $createAutoincfield = civicrm_api3('Autoincfield', 'create', $args);
    $customFieldID = $latestCustomField['id'];
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
}

/**
 * Implements hook_civicrm_post().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
 */
function autoincfield_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($op == 'create') {
    $subTypes = NULL;
    $subName = NULL;

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

        $fields = CRM_Core_BAO_CustomField::getFields($objectName);
        foreach ($fields as $field) {
          if ($field['extends_entity_column_id'] == 1) {
            $customDataType = CRM_Core_OptionGroup::values('custom_data_type', FALSE, FALSE, FALSE, NULL, 'name');
            $roleCustomDataTypeID = array_search('ParticipantRole', $customDataType);
            $subTypes = $objectRef->role_id;
            $subName = $roleCustomDataTypeID;
          } else if ($field['extends_entity_column_id'] == 3) {
            $customDataType = CRM_Core_OptionGroup::values('custom_data_type', FALSE, FALSE, FALSE, NULL, 'name');
            $eventTypeID = CRM_Core_DAO::getFieldValue("CRM_Event_DAO_Event", $objectRef->event_id, 'event_type_id', 'id');
            $eventTypeCustomDataTypeID = array_search('ParticipantEventType', $customDataType);
            $subTypes = $eventTypeID;
            $subName = $eventTypeCustomDataTypeID;
          }
        }
        break;
    }

    $customGroupFieldTree = CRM_Core_BAO_CustomGroup::getTree(
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

    $fields = CRM_Core_BAO_CustomField::getFields($objectName);
    $getAutoincfield = civicrm_api3('Autoincfield', 'get', []);
    $autoinc = [];

    foreach ($getAutoincfield as $autoincfield) {
      $autoinc[$autoincfield['custom_field_id']]['id'] = $autoincfield['custom_field_id'];
      $autoinc[$autoincfield['custom_field_id']]['min_value'] = $autoincfield['min_value'];
    }

    foreach ($customGroupFieldTree as $customFields) {
      if (!empty($customFields['fields'])) {
        foreach ($customFields['fields'] as $field) {
          if (!empty($autoinc[$field['id']]) && in_array($field['id'], $autoinc[$field['id']])) {
            $fieldID = $field['id'];
            $timestamp = date('Y-m-d H:i:s');
            $autoincValue = $autoinc[$fieldID]['min_value'];
            $hasValue = CRM_Core_DAO::checkFieldHasAlwaysValue("civicrm_autoincfield_$fieldID", 'counter', '');
            // $text = json_encode($customGroupFieldTree);

            if (!$hasValue) {
              $autoincValue = '';
            }

            $sql = "INSERT INTO `civicrm_autoincfield_$fieldID` (`counter`,`timestamp`, `text`) VALUES ('$autoincValue', '$timestamp')";
            CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );
          }
        }
      }
    }
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
