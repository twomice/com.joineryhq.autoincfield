<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from C:\xampp2\htdocs\drupal\sites\default\files\civicrm\ext\com.joineryhq.autoincfield\xml/schema/CRM/Autoincfield/Autoincfield.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:ddc8b14e89ec4c810351823595c3cbd5)
 */

/**
 * Database access object for the Autoincfield entity.
 */
class CRM_Autoincfield_DAO_Autoincfield extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_autoincfield';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique Autoincfield ID
   *
   * @var int
   */
  public $id;

  /**
   * FK to Custom Field ID
   *
   * @var int
   */
  public $custom_field_id;

  /**
   * Integer, the minimum value upon next usage
   *
   * @var int
   */
  public $min_value;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_autoincfield';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   */
  public static function getEntityTitle() {
    return ts('Autoincfields');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'custom_field_id', 'civicrm_custom_field', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => CRM_Autoincfield_ExtensionUtil::ts('Unique Autoincfield ID'),
          'required' => TRUE,
          'where' => 'civicrm_autoincfield.id',
          'table_name' => 'civicrm_autoincfield',
          'entity' => 'Autoincfield',
          'bao' => 'CRM_Autoincfield_DAO_Autoincfield',
          'localizable' => 0,
          'add' => NULL,
        ],
        'custom_field_id' => [
          'name' => 'custom_field_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => CRM_Autoincfield_ExtensionUtil::ts('FK to Custom Field ID'),
          'where' => 'civicrm_autoincfield.custom_field_id',
          'table_name' => 'civicrm_autoincfield',
          'entity' => 'Autoincfield',
          'bao' => 'CRM_Autoincfield_DAO_Autoincfield',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_CustomField',
          'add' => NULL,
        ],
        'min_value' => [
          'name' => 'min_value',
          'type' => CRM_Utils_Type::T_INT,
          'title' => CRM_Autoincfield_ExtensionUtil::ts('Min Value'),
          'description' => CRM_Autoincfield_ExtensionUtil::ts('Integer, the minimum value upon next usage'),
          'where' => 'civicrm_autoincfield.min_value',
          'table_name' => 'civicrm_autoincfield',
          'entity' => 'Autoincfield',
          'bao' => 'CRM_Autoincfield_DAO_Autoincfield',
          'localizable' => 0,
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'autoincfield', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'autoincfield', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
