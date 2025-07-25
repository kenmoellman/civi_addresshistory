<?php
// File: Civi/Api4/AddressHistory.php

namespace Civi\Api4;

/**
 * AddressHistory entity.
 *
 * Provides API access to address history records.
 *
 * @searchable secondary
 * @since 0.9.0
 * @package Civi\Api4
 */
class AddressHistory extends Generic\DAOEntity {

  /**
   * Get permissions.
   */
  public static function permissions() {
    return [
      'meta' => ['access CiviCRM'],
      'default' => [
        // Use contact permissions since address history is contact-related
        ['access CiviCRM', 'view all contacts'],
        ['access CiviCRM', 'view my contact'],
      ],
      'get' => [
        ['access CiviCRM', 'view all contacts'],
        ['access CiviCRM', 'view my contact'],
      ],
      'create' => [
        ['access CiviCRM', 'edit all contacts'],
        ['access CiviCRM', 'edit my contact'],
      ],
      'update' => [
        ['access CiviCRM', 'edit all contacts'],
        ['access CiviCRM', 'edit my contact'],
      ],
      'delete' => [
        ['access CiviCRM', 'edit all contacts'],
        ['access CiviCRM', 'edit my contact'],
      ],
    ];
  }

  /**
   * Get entity title.
   *
   * @return string
   */
  protected static function getEntityTitle() {
    return ts('Address History');
  }

  /**
   * Get entity description.
   *
   * @return string
   */
  protected static function getEntityDescription() {
    return ts('Historical address records for contacts with start and end dates');
  }

  /**
   * Get BAO class name.
   *
   * @return string
   */
  protected static function getBAOClass() {
    return 'CRM_Addresshistory_BAO_AddressHistory';
  }

  /**
   * Get DAO class name.
   *
   * @return string
   */
  protected static function getDAOClass() {
    return 'CRM_Addresshistory_DAO_AddressHistory';
  }

  /**
   * Get table name.
   *
   * @return string
   */
  protected static function getTableName() {
    return 'civicrm_address_history';
  }

  /**
   * Get primary key column.
   *
   * @return string
   */
  protected static function getPrimaryKey() {
    return 'id';
  }

  /**
   * Check permissions for a given entity and action.
   *
   * @param string $action
   * @param array $record
   * @param int $userID
   * @return bool
   */
  public static function checkAccess($action, $record = [], $userID = NULL) {
    // For address history, check permissions based on the related contact
    if (!empty($record['contact_id'])) {
      $contactId = $record['contact_id'];
      
      // Check if user has permission to view/edit this contact
      switch ($action) {
        case 'get':
        case 'view':
          return \CRM_Contact_BAO_Contact_Permission::allow($contactId, \CRM_Core_Permission::VIEW, $userID);
          
        case 'create':
        case 'update':
        case 'delete':
          return \CRM_Contact_BAO_Contact_Permission::allow($contactId, \CRM_Core_Permission::EDIT, $userID);
          
        default:
          return parent::checkAccess($action, $record, $userID);
      }
    }
    
    return parent::checkAccess($action, $record, $userID);
  }

}
