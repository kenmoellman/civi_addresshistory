<?php
// File: api/v3/AddressHistory.php

/**
 * AddressHistory.Get API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_address_history_get_spec(&$spec) {
  $spec['contact_id'] = [
    'title' => 'Contact ID',
    'description' => 'Contact ID to get address history for',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
  $spec['location_type_id'] = [
    'title' => 'Location Type ID',
    'description' => 'Filter by location type',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['start_date'] = [
    'title' => 'Start Date',
    'description' => 'Filter by start date',
    'type' => CRM_Utils_Type::T_DATE,
  ];
  $spec['end_date'] = [
    'title' => 'End Date',
    'description' => 'Filter by end date',
    'type' => CRM_Utils_Type::T_DATE,
  ];
}

/**
 * AddressHistory.Get API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_address_history_get($params) {
  try {
    $contactId = $params['contact_id'];
    
    // Check permission
    if (!CRM_Contact_BAO_Contact_Permission::allow($contactId)) {
      throw new API_Exception('Permission denied');
    }
    
    $results = CRM_Addresshistory_BAO_AddressHistory::getAddressHistory($contactId, $params);
    
    return civicrm_api3_create_success($results, $params, 'AddressHistory', 'get');
  }
  catch (Exception $e) {
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * AddressHistory.Create API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_address_history_create_spec(&$spec) {
  $spec['contact_id'] = [
    'title' => 'Contact ID',
    'description' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
  $spec['location_type_id'] = [
    'title' => 'Location Type ID',
    'description' => 'Location type ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['street_address'] = [
    'title' => 'Street Address',
    'description' => 'Street address',
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['city'] = [
    'title' => 'City',
    'description' => 'City',
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['postal_code'] = [
    'title' => 'Postal Code',
    'description' => 'Postal code',
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['state_province_id'] = [
    'title' => 'State/Province ID',
    'description' => 'State/Province ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['country_id'] = [
    'title' => 'Country ID',
    'description' => 'Country ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['start_date'] = [
    'title' => 'Start Date',
    'description' => 'Start date for this address',
    'type' => CRM_Utils_Type::T_DATE,
  ];
  $spec['end_date'] = [
    'title' => 'End Date',
    'description' => 'End date for this address',
    'type' => CRM_Utils_Type::T_DATE,
  ];
}

/**
 * AddressHistory.Create API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_address_history_create($params) {
  try {
    $contactId = $params['contact_id'];
    
    // Check permission
    if (!CRM_Contact_BAO_Contact_Permission::allow($contactId, CRM_Core_Permission::EDIT)) {
      throw new API_Exception('Permission denied');
    }
    
    $history = new CRM_Addresshistory_DAO_AddressHistory();
    $history->copyValues($params);
    $history->save();
    
    $result = $history->toArray();
    
    return civicrm_api3_create_success([$result], $params, 'AddressHistory', 'create');
  }
  catch (Exception $e) {
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * AddressHistory.Delete API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_address_history_delete_spec(&$spec) {
  $spec['id'] = [
    'title' => 'Address History ID',
    'description' => 'Address History ID to delete',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
}

/**
 * AddressHistory.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_address_history_delete($params) {
  try {
    $id = $params['id'];
    
    // Get the record first to check permissions
    $history = new CRM_Addresshistory_DAO_AddressHistory();
    $history->id = $id;
    if (!$history->find(TRUE)) {
      throw new API_Exception('Address History record not found');
    }
    
    // Check permission
    if (!CRM_Contact_BAO_Contact_Permission::allow($history->contact_id, CRM_Core_Permission::EDIT)) {
      throw new API_Exception('Permission denied');
    }
    
    $history->delete();
    
    return civicrm_api3_create_success([], $params, 'AddressHistory', 'delete');
  }
  catch (Exception $e) {
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * AddressHistory.Getcount API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_address_history_getcount($params) {
  try {
    $contactId = $params['contact_id'];
    
    // Check permission
    if (!CRM_Contact_BAO_Contact_Permission::allow($contactId)) {
      throw new API_Exception('Permission denied');
    }
    
    $count = CRM_Addresshistory_BAO_AddressHistory::getAddressHistoryCount($contactId);
    
    return civicrm_api3_create_success($count, $params, 'AddressHistory', 'getcount');
  }
  catch (Exception $e) {
    return civicrm_api3_create_error($e->getMessage());
  }
}
