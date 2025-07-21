<?php
// File: CRM/Addresshistory/Upgrader.php

use CRM_Addresshistory_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Addresshistory_Upgrader extends CRM_Addresshistory_Upgrader_Base {

  /**
   * Called during installation.
   */
  public function install() {
    $this->executeSqlFile('sql/install.sql');
    $this->createTriggers();
    $this->populateExistingAddresses();
  }

  /**
   * Called during uninstall.
   */
  public function uninstall() {
    $this->dropTriggers();
    $this->executeSqlFile('sql/uninstall.sql');
  }

  /**
   * Create database triggers for address history tracking.
   */
  public function createTriggers() {
    // Drop existing triggers first
    $this->dropTriggers();
    
    // Create INSERT trigger
    CRM_Core_DAO::executeQuery("
      CREATE TRIGGER civicrm_address_history_after_insert 
      AFTER INSERT ON civicrm_address
      FOR EACH ROW 
      BEGIN
          -- If this is a primary address, end any existing primary addresses for this contact/location type
          IF NEW.is_primary = 1 AND NEW.location_type_id IS NOT NULL THEN
              UPDATE civicrm_address_history 
              SET end_date = NOW() 
              WHERE contact_id = NEW.contact_id 
              AND location_type_id = NEW.location_type_id 
              AND is_primary = 1 
              AND (end_date IS NULL OR end_date > NOW());
          END IF;
          
          -- Insert new address history record
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
          ) VALUES (
              NEW.contact_id,
              NEW.location_type_id,
              NEW.is_primary,
              NEW.is_billing,
              NEW.street_address,
              NEW.street_number,
              NEW.street_number_suffix,
              NEW.street_number_predirectional,
              NEW.street_name,
              NEW.street_type,
              NEW.street_number_postdirectional,
              NEW.street_unit,
              NEW.supplemental_address_1,
              NEW.supplemental_address_2,
              NEW.supplemental_address_3,
              NEW.city,
              NEW.county_id,
              NEW.state_province_id,
              NEW.postal_code_suffix,
              NEW.postal_code,
              NEW.usps_adc,
              NEW.country_id,
              NEW.geo_code_1,
              NEW.geo_code_2,
              NEW.manual_geo_code,
              NEW.timezone,
              NEW.name,
              NEW.master_id,
              NOW(),
              NEW.id,
              NOW()
          );
      END
    ");

    // Create UPDATE trigger
    CRM_Core_DAO::executeQuery("
      CREATE TRIGGER civicrm_address_history_after_update 
      AFTER UPDATE ON civicrm_address
      FOR EACH ROW 
      BEGIN
          DECLARE significant_change BOOLEAN DEFAULT FALSE;
          DECLARE current_history_id INT DEFAULT NULL;
          
          -- Check if this is a significant change
          SET significant_change = (
              IFNULL(OLD.street_address, '') != IFNULL(NEW.street_address, '') OR
              IFNULL(OLD.city, '') != IFNULL(NEW.city, '') OR
              IFNULL(OLD.postal_code, '') != IFNULL(NEW.postal_code, '') OR
              IFNULL(OLD.state_province_id, 0) != IFNULL(NEW.state_province_id, 0) OR
              IFNULL(OLD.country_id, 0) != IFNULL(NEW.country_id, 0) OR
              IFNULL(OLD.location_type_id, 0) != IFNULL(NEW.location_type_id, 0) OR
              IFNULL(OLD.is_primary, 0) != IFNULL(NEW.is_primary, 0)
          );
          
          -- Get current active history record for this address
          SELECT id INTO current_history_id 
          FROM civicrm_address_history 
          WHERE original_address_id = NEW.id 
          AND (end_date IS NULL OR end_date > NOW())
          ORDER BY start_date DESC 
          LIMIT 1;
          
          IF significant_change THEN
              -- End the current history record if it exists
              IF current_history_id IS NOT NULL THEN
                  UPDATE civicrm_address_history 
                  SET end_date = NOW() 
                  WHERE id = current_history_id;
              END IF;
              
              -- If this is now a primary address, end any other primary addresses for this contact/location type
              IF NEW.is_primary = 1 AND NEW.location_type_id IS NOT NULL THEN
                  UPDATE civicrm_address_history 
                  SET end_date = NOW() 
                  WHERE contact_id = NEW.contact_id 
                  AND location_type_id = NEW.location_type_id 
                  AND is_primary = 1 
                  AND id != IFNULL(current_history_id, 0)
                  AND (end_date IS NULL OR end_date > NOW());
              END IF;
              
              -- Create new history record with updated data
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
              ) VALUES (
                  NEW.contact_id,
                  NEW.location_type_id,
                  NEW.is_primary,
                  NEW.is_billing,
                  NEW.street_address,
                  NEW.street_number,
                  NEW.street_number_suffix,
                  NEW.street_number_predirectional,
                  NEW.street_name,
                  NEW.street_type,
                  NEW.street_number_postdirectional,
                  NEW.street_unit,
                  NEW.supplemental_address_1,
                  NEW.supplemental_address_2,
                  NEW.supplemental_address_3,
                  NEW.city,
                  NEW.county_id,
                  NEW.state_province_id,
                  NEW.postal_code_suffix,
                  NEW.postal_code,
                  NEW.usps_adc,
                  NEW.country_id,
                  NEW.geo_code_1,
                  NEW.geo_code_2,
                  NEW.manual_geo_code,
                  NEW.timezone,
                  NEW.name,
                  NEW.master_id,
                  NOW(),
                  NEW.id,
                  NOW()
              );
          ELSE
              -- Minor change - just update the existing history record
              IF current_history_id IS NOT NULL THEN
                  UPDATE civicrm_address_history SET
                      location_type_id = NEW.location_type_id,
                      is_primary = NEW.is_primary,
                      is_billing = NEW.is_billing,
                      street_address = NEW.street_address,
                      street_number = NEW.street_number,
                      street_number_suffix = NEW.street_number_suffix,
                      street_number_predirectional = NEW.street_number_predirectional,
                      street_name = NEW.street_name,
                      street_type = NEW.street_type,
                      street_number_postdirectional = NEW.street_number_postdirectional,
                      street_unit = NEW.street_unit,
                      supplemental_address_1 = NEW.supplemental_address_1,
                      supplemental_address_2 = NEW.supplemental_address_2,
                      supplemental_address_3 = NEW.supplemental_address_3,
                      city = NEW.city,
                      county_id = NEW.county_id,
                      state_province_id = NEW.state_province_id,
                      postal_code_suffix = NEW.postal_code_suffix,
                      postal_code = NEW.postal_code,
                      usps_adc = NEW.usps_adc,
                      country_id = NEW.country_id,
                      geo_code_1 = NEW.geo_code_1,
                      geo_code_2 = NEW.geo_code_2,
                      manual_geo_code = NEW.manual_geo_code,
                      timezone = NEW.timezone,
                      name = NEW.name,
                      master_id = NEW.master_id,
                      modified_date = NOW()
                  WHERE id = current_history_id;
              END IF;
          END IF;
      END
    ");

    // Create DELETE trigger
    CRM_Core_DAO::executeQuery("
      CREATE TRIGGER civicrm_address_history_after_delete 
      AFTER DELETE ON civicrm_address
      FOR EACH ROW 
      BEGIN
          -- End any active history records for this address
          UPDATE civicrm_address_history 
          SET end_date = NOW() 
          WHERE original_address_id = OLD.id 
          AND (end_date IS NULL OR end_date > NOW());
      END
    ");
  }

  /**
   * Drop database triggers.
   */
  public function dropTriggers() {
    $triggers = [
      'civicrm_address_history_after_insert',
      'civicrm_address_history_after_update',
      'civicrm_address_history_after_delete'
    ];
    
    foreach ($triggers as $trigger) {
      CRM_Core_DAO::executeQuery("DROP TRIGGER IF EXISTS {$trigger}");
    }
  }

  /**
   * Populate existing addresses into the history table.
   */
  public function populateExistingAddresses() {
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
  }

}
