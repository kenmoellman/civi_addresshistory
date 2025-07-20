<?php
// File: CRM/Addresshistory/DAO/AddressHistory.php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */

/**
 * Database access object for the AddressHistory entity.
 */
class CRM_Addresshistory_DAO_AddressHistory extends CRM_Core_DAO {

  public static $_tableName = 'civicrm_address_history';

  /**
   * Should CiviCRM log any modifications to this table in the log tables.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique AddressHistory ID
   *
   * @var int
   */
  public $id;

  /**
   * FK to Contact ID
   *
   * @var int
   */
  public $contact_id;

  /**
   * FK to civicrm_location_type
   *
   * @var int
   */
  public $location_type_id;

  /**
   * Is this the primary address for this contact and location type
   *
   * @var bool
   */
  public $is_primary;

  /**
   * Is this the billing address
   *
   * @var bool
   */
  public $is_billing;

  /**
   * Concatenation of all routable street address components
   *
   * @var string
   */
  public $street_address;

  /**
   * Numeric portion of address number on the street
   *
   * @var int
   */
  public $street_number;

  /**
   * Non-numeric portion of address number on the street
   *
   * @var string
   */
  public $street_number_suffix;

  /**
   * Directional prefix
   *
   * @var string
   */
  public $street_number_predirectional;

  /**
   * Actual street name
   *
   * @var string
   */
  public $street_name;

  /**
   * St, Rd, Ave, etc.
   *
   * @var string
   */
  public $street_type;

  /**
   * Directional suffix
   *
   * @var string
   */
  public $street_number_postdirectional;

  /**
   * Secondary unit designator
   *
   * @var string
   */
  public $street_unit;

  /**
   * Supplemental Address Information, Line 1
   *
   * @var string
   */
  public $supplemental_address_1;

  /**
   * Supplemental Address Information, Line 2
   *
   * @var string
   */
  public $supplemental_address_2;

  /**
   * Supplemental Address Information, Line 3
   *
   * @var string
   */
  public $supplemental_address_3;

  /**
   * City, Town or Village name
   *
   * @var string
   */
  public $city;

  /**
   * FK to civicrm_county
   *
   * @var int
   */
  public $county_id;

  /**
   * FK to civicrm_state_province
   *
   * @var int
   */
  public $state_province_id;

  /**
   * Store the suffix, like the +4 part in the USPS system
   *
   * @var string
   */
  public $postal_code_suffix;

  /**
   * Store both US (zip5) and international postal codes
   *
   * @var string
   */
  public $postal_code;

  /**
   * USPS Bulk mailing code
   *
   * @var string
   */
  public $usps_adc;

  /**
   * FK to civicrm_country
   *
   * @var int
   */
  public $country_id;

  /**
   * Latitude
   *
   * @var float
   */
  public $geo_code_1;

  /**
   * Longitude
   *
   * @var float
   */
  public $geo_code_2;

  /**
   * Is this a manually entered geo code
   *
   * @var bool
   */
  public $manual_geo_code;

  /**
   * Timezone expressed as a UTC offset
   *
   * @var string
   */
  public $timezone;

  /**
   * Name of the address or location
   *
   * @var string
   */
  public $name;

  /**
   * FK to civicrm_address
   *
   * @var int
   */
  public $master_id;

  /**
   * Start date for this address
   *
   * @var string
   */
  public $start_date;

  /**
   * End date for this address
   *
   * @var string
   */
  public $end_date;

  /**
   * When was this address history record created
   *
   * @var string
   */
  public $created_date;

  /**
   * When was this address history record last modified
   *
   * @var string
   */
  public $modified_date;

  /**
   * Original address ID from civicrm_address
   *
   * @var int
   */
  public $original_address_id;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_address_history';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? ts('Address Histories') : ts('Address History');
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
          'title' => ts('Address History ID'),
          'description' => ts('Unique AddressHistory ID'),
          'required' => TRUE,
          'where' => 'civicrm_address_history.id',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Contact ID'),
          'description' => ts('FK to Contact ID'),
          'required' => TRUE,
          'where' => 'civicrm_address_history.contact_id',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
        ],
        'location_type_id' => [
          'name' => 'location_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Location Type ID'),
          'description' => ts('FK to civicrm_location_type'),
          'where' => 'civicrm_address_history.location_type_id',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_LocationType',
        ],
        'is_primary' => [
          'name' => 'is_primary',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Primary Address'),
          'description' => ts('Is this the primary address for this contact and location type'),
          'where' => 'civicrm_address_history.is_primary',
          'default' => '0',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'street_address' => [
          'name' => 'street_address',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Street Address'),
          'description' => ts('Concatenation of all routable street address components'),
          'maxlength' => 96,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_address_history.street_address',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'city' => [
          'name' => 'city',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('City'),
          'description' => ts('City, Town or Village name'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_address_history.city',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'postal_code' => [
          'name' => 'postal_code',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Postal Code'),
          'description' => ts('Store both US (zip5) and international postal codes'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_address_history.postal_code',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'state_province_id' => [
          'name' => 'state_province_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('State/Province ID'),
          'description' => ts('FK to civicrm_state_province'),
          'where' => 'civicrm_address_history.state_province_id',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_StateProvince',
        ],
        'country_id' => [
          'name' => 'country_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Country ID'),
          'description' => ts('FK to civicrm_country'),
          'where' => 'civicrm_address_history.country_id',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Country',
        ],
        'start_date' => [
          'name' => 'start_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Start Date'),
          'description' => ts('Start date for this address'),
          'where' => 'civicrm_address_history.start_date',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'end_date' => [
          'name' => 'end_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('End Date'),
          'description' => ts('End date for this address'),
          'where' => 'civicrm_address_history.end_date',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
        'original_address_id' => [
          'name' => 'original_address_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Original Address ID'),
          'description' => ts('Original address ID from civicrm_address'),
          'where' => 'civicrm_address_history.original_address_id',
          'table_name' => 'civicrm_address_history',
          'entity' => 'AddressHistory',
          'bao' => 'CRM_Addresshistory_DAO_AddressHistory',
          'localizable' => 0,
        ],
      ];
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Returns an array containing, for each field, the array key used for that
   * field in the API request.
   *
   * @return array
   *   Field name => API field name
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

}
