<?php
// File: CRM/Addresshistory/BAO/AddressHistory.php

class CRM_Addresshistory_BAO_AddressHistory extends CRM_Addresshistory_DAO_AddressHistory {

  /**
   * Get address history for a contact.
   *
   * @param int $contactId
   * @param array $params
   * @return array
   */
  public static function getAddressHistory($contactId, $params = []) {
    $whereClause = "WHERE ah.contact_id = %1";
    $queryParams = [1 => [$contactId, 'Integer']];
    
    if (!empty($params['location_type_id'])) {
      $whereClause .= " AND ah.location_type_id = %2";
      $queryParams[2] = [$params['location_type_id'], 'Integer'];
    }
    
    $orderBy = "ORDER BY ah.start_date DESC, ah.location_type_id, ah.is_primary DESC";
    
    $query = "
      SELECT ah.*, 
             lt.display_name as location_type_name,
             sp.name as state_province_name,
             c.name as country_name
      FROM civicrm_address_history ah
      LEFT JOIN civicrm_location_type lt ON ah.location_type_id = lt.id
      LEFT JOIN civicrm_state_province sp ON ah.state_province_id = sp.id
      LEFT JOIN civicrm_country c ON ah.country_id = c.id
      {$whereClause}
      {$orderBy}
    ";
    
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    $results = [];
    
    while ($dao->fetch()) {
      $results[] = $dao->toArray();
    }
    
    return $results;
  }

  /**
   * Get count of address history records for a contact.
   *
   * @param int $contactId
   * @return int
   */
  public static function getAddressHistoryCount($contactId) {
    $query = "SELECT COUNT(*) FROM civicrm_address_history WHERE contact_id = %1";
    return CRM_Core_DAO::singleValueQuery($query, [1 => [$contactId, 'Integer']]);
  }

  /**
   * Merge address history during contact merge.
   *
   * @param int $mainId
   * @param int $otherId
   */
  public static function mergeAddressHistory($mainId, $otherId) {
    // Update all address history records from the duplicate contact
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_address_history 
      SET contact_id = %1 
      WHERE contact_id = %2
    ", [
      1 => [$mainId, 'Integer'],
      2 => [$otherId, 'Integer']
    ]);
  }

  /**
   * Get formatted address history for display.
   *
   * @param int $contactId
   * @return array
   */
  public static function getFormattedAddressHistory($contactId) {
    $addressHistory = self::getAddressHistory($contactId);
    $formatted = [];
    
    foreach ($addressHistory as $history) {
      $address = [];
      
      // Build address line
      $addressParts = [];
      if (!empty($history['street_address'])) {
        $addressParts[] = $history['street_address'];
      }
      if (!empty($history['supplemental_address_1'])) {
        $addressParts[] = $history['supplemental_address_1'];
      }
      if (!empty($history['supplemental_address_2'])) {
        $addressParts[] = $history['supplemental_address_2'];
      }
      
      $address['street'] = implode(', ', $addressParts);
      
      // Build city, state, postal
      $cityStatePostal = [];
      if (!empty($history['city'])) {
        $cityStatePostal[] = $history['city'];
      }
      if (!empty($history['state_province_name'])) {
        $cityStatePostal[] = $history['state_province_name'];
      }
      if (!empty($history['postal_code'])) {
        $cityStatePostal[] = $history['postal_code'];
      }
      
      $address['city_state_postal'] = implode(', ', $cityStatePostal);
      $address['country'] = $history['country_name'];
      $address['location_type'] = $history['location_type_name'];
      $address['is_primary'] = $history['is_primary'];
      $address['start_date'] = $history['start_date'];
      $address['end_date'] = $history['end_date'];
      $address['id'] = $history['id'];
      
      $formatted[] = $address;
    }
    
    return $formatted;
  }

  /**
   * Populate address history with existing addresses from civicrm_address table.
   * Called during extension installation.
   */
  public static function populateExistingAddresses() {
    try {
      // Get all existing addresses
      $query = "
        INSERT INTO civicrm_address_history (
          contact_id,
          location_type_id,
          is_primary,
          is_billing,
          street_address,
          street_number,
          street_number_suffix,
          street_number_predirectional,
          street_name,
          street_type,
          street_number_postdirectional,
          street_unit,
          supplemental_address_1,
          supplemental_address_2,
          supplemental_address_3,
          city,
          county_id,
          state_province_id,
          postal_code_suffix,
          postal_code,
          usps_adc,
          country_id,
          geo_code_1,
          geo_code_2,
          manual_geo_code,
          timezone,
          name,
          master_id,
          start_date,
          original_address_id,
          created_date
        )
        SELECT 
          contact_id,
          location_type_id,
          is_primary,
          is_billing,
          street_address,
          street_number,
          street_number_suffix,
          street_number_predirectional,
          street_name,
          street_type,
          street_number_postdirectional,
          street_unit,
          supplemental_address_1,
          supplemental_address_2,
          supplemental_address_3,
          city,
          county_id,
          state_province_id,
          postal_code_suffix,
          postal_code,
          usps_adc,
          country_id,
          geo_code_1,
          geo_code_2,
          manual_geo_code,
          timezone,
          name,
          master_id,
          COALESCE(
            (SELECT MIN(log_date) FROM civicrm_log WHERE entity_table = 'civicrm_address' AND entity_id = a.id AND log_action = 'Insert'),
            a.modified_date,
            a.created_date,
            NOW()
          ) as start_date,
          id as original_address_id,
          NOW() as created_date
        FROM civicrm_address a
        WHERE contact_id IS NOT NULL
      ";
      
      $result = CRM_Core_DAO::executeQuery($query);
      $count = CRM_Core_DAO::affectedRows();
      
      CRM_Core_Session::setStatus(
        ts('Successfully populated address history with %1 existing addresses.', [1 => $count]),
        ts('Address History Populated'),
        'success'
      );
      
      return $count;
      
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message('Error populating existing addresses: ' . $e->getMessage());
      CRM_Core_Session::setStatus(
        ts('Error populating address history. Check the error log for details.'),
        ts('Population Error'),
        'error'
      );
      return FALSE;
    }
  }

  /**
   * Check if address history triggers are installed and active.
   *
   * @return array Array with status information
   */
  public static function checkTriggerStatus() {
    $status = [
      'installed' => FALSE,
      'triggers' => [],
      'missing' => [],
    ];
    
    $expectedTriggers = [
      'civicrm_address_history_after_insert',
      'civicrm_address_history_after_update', 
      'civicrm_address_history_after_delete'
    ];
    
    try {
      $dao = CRM_Core_DAO::executeQuery("
        SHOW TRIGGERS LIKE 'civicrm_address'
      ");
      
      $foundTriggers = [];
      while ($dao->fetch()) {
        if (in_array($dao->Trigger, $expectedTriggers)) {
          $foundTriggers[] = $dao->Trigger;
          $status['triggers'][] = [
            'name' => $dao->Trigger,
            'event' => $dao->Event,
            'timing' => $dao->Timing,
          ];
        }
      }
      
      $status['missing'] = array_diff($expectedTriggers, $foundTriggers);
      $status['installed'] = count($foundTriggers) === count($expectedTriggers);
      
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message('Error checking address history triggers: ' . $e->getMessage());
    }
    
    return $status;
  }

}
