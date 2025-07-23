<?php
// File: CRM/Addresshistory/BAO/AddressHistory.php

use CRM_Addresshistory_ExtensionUtil as E;

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
   * Populate existing addresses into the history table.
   * 
   * @return bool
   */
  public static function populateExistingAddresses() {
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
        '2000-01-01 00:00:00' as start_date,
        id as original_address_id,
        NOW() as created_date
      FROM civicrm_address a
      WHERE contact_id IS NOT NULL
    ";
    
    $dao = CRM_Core_DAO::executeQuery($query);
    
    // Get the count of existing addresses for feedback
    $countQuery = "SELECT COUNT(*) FROM civicrm_address WHERE contact_id IS NOT NULL";
    $count = CRM_Core_DAO::singleValueQuery($countQuery);
    
    CRM_Core_Session::setStatus(
      E::ts('Successfully populated address history with %1 existing addresses.', [1 => $count]),
      E::ts('Address History Populated'),
      'success'
    );
    
    return TRUE;
  }

/**
   * Enhanced merge address history during contact merge.
   *
   * @param int $mainId Main contact ID (keeping this one)
   * @param int $otherId Other contact ID (deleting this one)
   * @param array $options Merge options (optional)
   */
  public static function mergeAddressHistory($mainId, $otherId, $options = []) {
    // Get the merge action from options or default to 'move'
    $action = CRM_Utils_Array::value('action', $options, 'move');
    
    CRM_Core_Error::debug_log_message("BAO mergeAddressHistory called - MainID: {$mainId}, OtherID: {$otherId}, Action: {$action}");
    
    switch ($action) {
      case 'move':
        // Move all address history from other contact to main contact
        self::moveAddressHistory($mainId, $otherId);
        break;
        
      case 'copy':
        // Copy address history from other contact to main contact
        self::copyAddressHistory($mainId, $otherId);
        break;
        
      case 'keep':
        // Don't merge - keep address histories separate
        // (This means do nothing, but log it)
        CRM_Core_Error::debug_log_message("Address History: Keeping separate histories for contacts {$mainId} and {$otherId}");
        CRM_Core_Session::setStatus(
          ts('Address history kept separate - no records moved.'),
          ts('Address History'),
          'info'
        );
        break;
        
      default:
        // Default behavior - move
        self::moveAddressHistory($mainId, $otherId);
    }
  }

  /**
   * Move address history from one contact to another.
   *
   * @param int $mainId
   * @param int $otherId
   */
  private static function moveAddressHistory($mainId, $otherId) {
    CRM_Core_Error::debug_log_message("moveAddressHistory called - MainID: {$mainId}, OtherID: {$otherId}");
    
    // First check how many records we're about to move
    $countQuery = "SELECT COUNT(*) FROM civicrm_address_history WHERE contact_id = %1";
    $beforeCount = CRM_Core_DAO::singleValueQuery($countQuery, [1 => [$otherId, 'Integer']]);
    
    CRM_Core_Error::debug_log_message("moveAddressHistory - Found {$beforeCount} records to move");
    
    if ($beforeCount == 0) {
      CRM_Core_Session::setStatus(
        ts('No address history records found to move.'),
        ts('Address History'),
        'info'
      );
      return;
    }
    
    // Before moving, we need to understand the current address situation
    // Get current addresses for both contacts before the merge
    $mainCurrentAddresses = self::getCurrentAddressesByLocationTypeBeforeMerge($mainId);
    $otherCurrentAddresses = self::getCurrentAddressesByLocationTypeBeforeMerge($otherId);
    
    CRM_Core_Error::debug_log_message("moveAddressHistory - Main contact current addresses by location type: " . print_r(array_keys($mainCurrentAddresses), true));
    CRM_Core_Error::debug_log_message("moveAddressHistory - Other contact current addresses by location type: " . print_r(array_keys($otherCurrentAddresses), true));
    
    // Update all address history records from the duplicate contact
    CRM_Core_DAO::executeQuery("
      UPDATE civicrm_address_history 
      SET contact_id = %1 
      WHERE contact_id = %2
    ", [
      1 => [$mainId, 'Integer'],
      2 => [$otherId, 'Integer']
    ]);
    
    // Check how many records were actually moved by counting again
    $afterCount = CRM_Core_DAO::singleValueQuery($countQuery, [1 => [$otherId, 'Integer']]);
    $mainAfterCount = CRM_Core_DAO::singleValueQuery($countQuery, [1 => [$mainId, 'Integer']]);
    
    $actuallyMoved = $beforeCount - $afterCount;
    
    CRM_Core_Error::debug_log_message("moveAddressHistory - Actually moved {$actuallyMoved} records");
    
    // Now we need to fix the end dates based on what actually happened to the addresses
    self::fixEndDatesAfterMerge($mainId, $mainCurrentAddresses, $otherCurrentAddresses);
    
    CRM_Core_Error::debug_log_message("moveAddressHistory - After move: Other contact has {$afterCount} records, Main contact has {$mainAfterCount} records");
    
    if ($actuallyMoved > 0) {
      CRM_Core_Session::setStatus(
        ts('Moved %1 address history records from duplicate contact to main contact.', [1 => $actuallyMoved]),
        ts('Address History Merged'),
        'success'
      );
    } else {
      CRM_Core_Session::setStatus(
        ts('No address history records were moved. This may indicate an issue with the merge process.'),
        ts('Address History Warning'),
        'warning'
      );
    }
  }

  /**
   * Get current addresses for a contact by location type (before merge).
   *
   * @param int $contactId
   * @return array Array keyed by location_type_id with address IDs as values
   */
  private static function getCurrentAddressesByLocationTypeBeforeMerge($contactId) {
    $addresses = [];
    
    $dao = CRM_Core_DAO::executeQuery("
      SELECT id, location_type_id, is_primary
      FROM civicrm_address 
      WHERE contact_id = %1
    ", [1 => [$contactId, 'Integer']]);
    
    while ($dao->fetch()) {
      $addresses[$dao->location_type_id] = [
        'id' => $dao->id,
        'is_primary' => $dao->is_primary,
      ];
    }
    
    return $addresses;
  }

  /**
   * Fix end dates on address history records after a merge.
   *
   * @param int $mainId Main contact ID
   * @param array $mainCurrentAddresses Addresses main contact had before merge
   * @param array $otherCurrentAddresses Addresses other contact had before merge
   */
  private static function fixEndDatesAfterMerge($mainId, $mainCurrentAddresses, $otherCurrentAddresses) {
    CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Starting end date correction");
    
    // Get current addresses for main contact after merge
    $mainPostMergeAddresses = self::getCurrentAddressesByLocationTypeBeforeMerge($mainId);
    
    CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Main contact addresses after merge: " . print_r(array_keys($mainPostMergeAddresses), true));
    
    // For each location type that the other contact had
    foreach ($otherCurrentAddresses as $locationTypeId => $otherAddress) {
      $hadMainAddress = isset($mainCurrentAddresses[$locationTypeId]);
      $hasPostMergeAddress = isset($mainPostMergeAddresses[$locationTypeId]);
      
      CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Location type {$locationTypeId}: had main={$hadMainAddress}, has post-merge={$hasPostMergeAddress}");
      
      if (!$hasPostMergeAddress) {
        // Case 1: The other contact's address was NOT merged into main contact
        // This means the address was discarded, so end the history record
        CRM_Core_DAO::executeQuery("
          UPDATE civicrm_address_history 
          SET end_date = NOW() 
          WHERE contact_id = %1 
          AND location_type_id = %2 
          AND (end_date IS NULL OR end_date > NOW())
          AND original_address_id = %3
        ", [
          1 => [$mainId, 'Integer'],
          2 => [$locationTypeId, 'Integer'], 
          3 => [$otherAddress['id'], 'Integer'],
        ]);
        
        CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Ended address history for location type {$locationTypeId} (address not merged)");
        
      } else {
        // Case 2: The other contact's address WAS merged into main contact
        if ($hadMainAddress) {
          // Case 2a: Main contact already had an address of this type
          // The main contact's old address was replaced, so end its history
          CRM_Core_DAO::executeQuery("
            UPDATE civicrm_address_history 
            SET end_date = NOW() 
            WHERE contact_id = %1 
            AND location_type_id = %2 
            AND (end_date IS NULL OR end_date > NOW())
            AND original_address_id = %3
          ", [
            1 => [$mainId, 'Integer'],
            2 => [$locationTypeId, 'Integer'],
            3 => [$mainCurrentAddresses[$locationTypeId]['id'], 'Integer'],
          ]);
          
          CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Ended old main contact address history for location type {$locationTypeId} (replaced by other)");
          
          // The other contact's address becomes current (end_date = NULL), but we need to update its original_address_id
          CRM_Core_DAO::executeQuery("
            UPDATE civicrm_address_history 
            SET original_address_id = %3
            WHERE contact_id = %1 
            AND location_type_id = %2 
            AND (end_date IS NULL OR end_date > NOW())
            AND original_address_id = %4
          ", [
            1 => [$mainId, 'Integer'],
            2 => [$locationTypeId, 'Integer'],
            3 => [$mainPostMergeAddresses[$locationTypeId]['id'], 'Integer'],
            4 => [$otherAddress['id'], 'Integer'],
          ]);
          
        } else {
          // Case 2b: Main contact didn't have an address of this type
          // The other contact's address becomes current, update original_address_id
          CRM_Core_DAO::executeQuery("
            UPDATE civicrm_address_history 
            SET original_address_id = %3
            WHERE contact_id = %1 
            AND location_type_id = %2 
            AND (end_date IS NULL OR end_date > NOW())
            AND original_address_id = %4
          ", [
            1 => [$mainId, 'Integer'],
            2 => [$locationTypeId, 'Integer'],
            3 => [$mainPostMergeAddresses[$locationTypeId]['id'], 'Integer'],
            4 => [$otherAddress['id'], 'Integer'],
          ]);
          
          CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Updated original_address_id for location type {$locationTypeId} (new type for main contact)");
        }
      }
    }
    
    CRM_Core_Error::debug_log_message("fixEndDatesAfterMerge - Completed end date correction");
  }

  /**
   * Copy address history from one contact to another.
   *
   * @param int $mainId
   * @param int $otherId
   */
  private static function copyAddressHistory($mainId, $otherId) {
    // Get address history records from the other contact
    $query = "
      SELECT * FROM civicrm_address_history 
      WHERE contact_id = %1
      ORDER BY start_date DESC
    ";
    
    $dao = CRM_Core_DAO::executeQuery($query, [1 => [$otherId, 'Integer']]);
    $count = 0;
    
    while ($dao->fetch()) {
      // Create new record for main contact
      $insertQuery = "
        INSERT INTO civicrm_address_history (
          contact_id, location_type_id, is_primary, is_billing,
          street_address, supplemental_address_1, supplemental_address_2,
          city, state_province_id, postal_code, country_id,
          start_date, end_date, original_address_id, created_date
        ) VALUES (
          %1, %2, %3, %4, %5, %6, %7, %8, %9, %10, %11, %12, %13, %14, NOW()
        )
      ";
      
      CRM_Core_DAO::executeQuery($insertQuery, [
        1 => [$mainId, 'Integer'],
        2 => [$dao->location_type_id, 'Integer'],
        3 => [$dao->is_primary, 'Boolean'],
        4 => [$dao->is_billing, 'Boolean'],
        5 => [$dao->street_address, 'String'],
        6 => [$dao->supplemental_address_1, 'String'],
        7 => [$dao->supplemental_address_2, 'String'],
        8 => [$dao->city, 'String'],
        9 => [$dao->state_province_id, 'Integer'],
        10 => [$dao->postal_code, 'String'],
        11 => [$dao->country_id, 'Integer'],
        12 => [$dao->start_date, 'String'],
        13 => [$dao->end_date, 'String'],
        14 => [$dao->original_address_id, 'Integer'],
      ]);
      
      $count++;
    }
    
    CRM_Core_Session::setStatus(
      ts('Copied %1 address history records from duplicate contact to main contact.', [1 => $count]),
      ts('Address History Copied'),
      'success'
    );
  }

  /**
   * Get merge statistics for display.
   *
   * @param int $mainId
   * @param int $otherId
   * @return array
   */
  public static function getMergeStatistics($mainId, $otherId) {
    $mainCount = self::getAddressHistoryCount($mainId);
    $otherCount = self::getAddressHistoryCount($otherId);
    
    return [
      'main_count' => $mainCount,
      'other_count' => $otherCount,
      'total_after_merge' => $mainCount + $otherCount,
    ];
  }
}
