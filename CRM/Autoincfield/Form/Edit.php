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
      '1' => $autoincdetails['custom_group_title'],
    )));
  }

  public function buildQuickForm() {
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    $this->add('text',
      'autoincval',
      $autoincdetails['label'],
      '',
      TRUE
    );

    // Put the contactID and autoincID in a hidden text for the form validation
    $this->add('hidden',
      'contactID'
    );
    $this->add('hidden',
      'autoincID'
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

    $this->addFormRule(['CRM_Autoincfield_Form_Edit', 'formRule'], $this);

    parent::buildQuickForm();
  }

  /**
   * Set default values.
   *
   * @return array
   */
  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    $defaults['autoincval'] = $autoincdetails['autoincval'];
    $defaults['contactID'] = $this->_cid;
    $defaults['autoincID'] = $this->_id;

    return $defaults;
  }

  /**
   * Global validation rules for the form.
   *
   * @param array $values
   *   Posted values of the form.
   *
   * @return array
   *   list of errors to be posted back to the form
   */
  public function formRule($values) {
    $errors = [];
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($values['autoincID'], $values['contactID']);

    if (!empty($values['autoincval']) && $autoincdetails['autoincval'] != $values['autoincval']) {
      if ($autoincdetails['min_value'] < $values['autoincval']) {
        $errors['autoincval'] = E::ts(
          'Value should be equal or lower than the minimum value of the autoincfield custom field. Current minimum value: %1',
          array(
            1 => $autoincdetails['min_value'],
          )
        );
      }

      $result = CRM_Core_DAO::executeQuery("SELECT * FROM `{$autoincdetails['table_name']}` WHERE `{$autoincdetails['column_name']}` = {$values['autoincval']}");
      if ($result->fetch()) {
        $errors['autoincval'] = E::ts('Value already exist in a user.');
      }
    }

    return $errors;
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    $values = $this->exportValues();
    $autoincdetails = CRM_Autoincfield_Settings::getAutoincDetails($this->_id, $this->_cid);

    // If the value is still the same, just return
    if ($values['autoincval'] === $autoincdetails['autoincval']) {
      CRM_Core_Session::setStatus(E::ts('No changes made.'), $autoincdetails['custom_group_title'], 'success');
      return;
    }

    $result = CRM_Core_DAO::executeQuery("SELECT * FROM `{$autoincdetails['table_name']}` WHERE `entity_id` = %0", array(
      0 => array($this->_cid, 'Integer'),
    ));
    if ($result->fetch()) {
      $query = "UPDATE `{$autoincdetails['table_name']}` SET `{$autoincdetails['column_name']}` = %0 WHERE `entity_id` = %1";
    }
    else {
      $query = "INSERT INTO `{$autoincdetails['table_name']}` (`entity_id`, `{$autoincdetails['column_name']}`) VALUES (%1, %0)";
    }

    CRM_Core_DAO::executeQuery($query, array(
      0 => array($values['autoincval'], 'Integer'),
      1 => array($this->_cid, 'Integer'),
    ));

    CRM_Core_Session::setStatus(
      E::ts(
        '%1 Saved',
        array(
          1 => $autoincdetails['label'],
        )
      ),
      $autoincdetails['custom_group_title'],
      'success'
    );

    parent::postProcess();
  }

}
