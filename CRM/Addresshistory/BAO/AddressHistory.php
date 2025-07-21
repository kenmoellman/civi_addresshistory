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

  /**
   * Populate address history with existing addresses from civicrm_address table.
   * This method is called during extension installation to create historical records
   * for all existing addresses with a default start date.
   *
   * @return int Number of addresses populated
   * @throws Exception If there's an error during population
   */
  public static function populateExistingAddresses() {
    $count = 0;
    
    try {
      // Log the start of population
      CRM_Core_Error::debug_log_message('Address History: Starting population of existing addresses');
      
      // Get all existing addresses with valid contact_id
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
          created_date,
          original_address_id
        )
        SELECT 
          a.contact_id,
          a.location_type_id,
          a.is_primary,
          a.is_billing,
          a.street_address,
          a.street_number,
          a.street_number_suffix,
          a.street_number_predirectional,
          a.street_name,
          a.street_type,
          a.street_number_postdirectional,
          a.street_unit,
          a.supplemental_address_1,
          a.supplemental_address_2,
          a.supplemental_address_3,
          a.city,
          a.county_id,
          a.state_province_id,
          a.postal_code_suffix,
          a.postal_code,
          a.usps_adc,
          a.country_id,
          a.geo_code_1,
          a.geo_code_2,
          a.manual_geo_code,
          a.timezone,
          a.name,
          a.master_id,
          '2000-01-01 00:00:00' as start_date,
          NOW() as created_date,
          a.id as original_address_id
        FROM civicrm_address a
        WHERE a.contact_id IS NOT NULL
          AND a.contact_id > 0
          AND NOT EXISTS (
            SELECT 1 FROM civicrm_address_history ah 
            WHERE ah.original_address_id = a.id
          )
      ";
      
      // Execute the query
      $dao = CRM_Core_DAO::executeQuery($query);
      $count = CRM_Core_DAO::affectedRows();
      
      // Log successful population
      CRM_Core_Error::debug_log_message("Address History: Successfully populated {$count} existing addresses");
      
    } catch (Exception $e) {
      $errorMessage = 'Error populating existing addresses: ' . $e->getMessage();
      CRM_Core_Error::debug_log_message('Address History: ' . $errorMessage);
      throw new Exception($errorMessage);
    }
    
    return $count;
  }
}

