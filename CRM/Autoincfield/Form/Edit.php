<?php

use CRM_Autoincfield_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Autoincfield_Form_Edit extends CRM_Core_Form {

  /**
   * Custom Field ID
   * @var int
   */
  private $_id;

  /**
   * Contact ID
   * @var int
   */
  private $_cid;

  /**
   * Pre-process
   */
  public function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive',
      $this, FALSE, 0
    );

    $this->_cid = CRM_Utils_Request::retrieve('cid', 'Positive',
      $this, FALSE, 0
    );

    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    CRM_Utils_System::setTitle(E::ts('Edit Autoincfield: %1', array(
      '1' => $autoincdetails['custom_group_title']
    )));
  }

  public function buildQuickForm() {
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    $this->add('text',
      'autoincval',
      $autoincdetails['label'],
      TRUE
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ),
    ));

    parent::buildQuickForm();
  }

  /**
   * Set default values for the form.
   */
  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    $defaults['autoincval'] = $autoincdetails['autoincval'];

    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    $query = "UPDATE `{$autoincdetails['table_name']}` SET `{$autoincdetails['column_name']}` = %0 WHERE `entity_id` = %1";
    CRM_Core_DAO::executeQuery($query, array(
      0 => array($values['autoincval'], 'Integer'),
      1 => array($this->_cid, 'Integer'),
    ));

    parent::postProcess();
  }

}
