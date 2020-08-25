<?php

require_once 'autoincfield.civix.php';
// phpcs:disable
use CRM_Autoincfield_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function autoincfield_civicrm_buildForm($formName, &$form) {
  if($formName == 'CRM_Custom_Form_Field') {
    if ( $form->elementExists( 'data_type' ) ) {
      $dataTypes = $form->getElement('data_type');
      $newDataTypePlacement = count($dataTypes->_elements[0]->_options);

      $autoIncArr = array(
        'text' => 'Autoincrement',
        'attr' => array(
          'value' => $newDataTypePlacement,
        ),
      );

      array_push($dataTypes->_elements[0]->_options, $autoIncArr);

      $tpl = CRM_Core_Smarty::singleton();
      $templatePath = $tpl->template_dir[0];
      $form->addElement('text', 'min_value', ts('Minimum next value'));
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => $templatePath . '/MinValueField.tpl',
      ));

      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.autoincfield', 'js/autoincfield.js');
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
