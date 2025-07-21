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
  }

  /**
   * Called after installation.
   */
  public function postInstall() {
    // Populate existing addresses into history table
    CRM_Addresshistory_BAO_AddressHistory::populateExistingAddresses();
  }

  /**
   * Called during uninstall.
   */
  public function uninstall() {
    $this->dropTriggers();
    $this->executeSqlFile('sql/uninstall.sql');
  }

  /**
   * Called during enable.
   */
  public function enable() {
    $this->createTriggers();
  }

  /**
   * Called during disable.
   */
  public function disable() {
    $this->dropTriggers();
  }

  /**
   * Create database triggers for address history tracking.
   */
  public function createTriggers() {
    // Drop existing triggers first
    $this->dropTriggers();
    
    // Create triggers manually (more reliable than parsing SQL file)
    $this->createTriggersManually();
  }

  /**
   * Create triggers manually if SQL file is not available.
   */
  private function createTriggersManually() {
    try {
      // Create INSERT trigger
      CRM_Core_DAO::executeQuery("
        CREATE TRIGGER civicrm_address_history_after_insert 
        AFTER INSERT ON civicrm_address
        FOR EACH ROW 
        BEGIN
            IF NEW.contact_id IS NOT NULL THEN
                IF NEW.is_primary = 1 AND NEW.location_type_id IS NOT NULL THEN
                    UPDATE civicrm_address_history 
                    SET end_date = NOW() 
                    WHERE contact_id = NEW.contact_id 
                    AND location_type_id = NEW.location_type_id 
                    AND is_primary = 1 
                    AND (end_date IS NULL OR end_date > NOW());
                END IF;
                
                INSERT INTO civicrm_address_history (
                    contact_id, location_type_id, is_primary, is_billing,
                    street_address, supplemental_address_1, supplemental_address_2,
                    city, state_province_id, postal_code, country_id,
                    start_date, original_address_id, created_date
                ) VALUES (
                    NEW.contact_id, NEW.location_type_id, NEW.is_primary, NEW.is_billing,
                    NEW.street_address, NEW.supplemental_address_1, NEW.supplemental_address_2,
                    NEW.city, NEW.state_province_id, NEW.postal_code, NEW.country_id,
                    NOW(), NEW.id, NOW()
                );
            END IF;
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
            
            IF NEW.contact_id IS NOT NULL THEN
                SET significant_change = (
                    IFNULL(OLD.street_address, '') != IFNULL(NEW.street_address, '') OR
                    IFNULL(OLD.city, '') != IFNULL(NEW.city, '') OR
                    IFNULL(OLD.postal_code, '') != IFNULL(NEW.postal_code, '') OR
                    IFNULL(OLD.state_province_id, 0) != IFNULL(NEW.state_province_id, 0) OR
                    IFNULL(OLD.country_id, 0) != IFNULL(NEW.country_id, 0) OR
                    IFNULL(OLD.location_type_id, 0) != IFNULL(NEW.location_type_id, 0) OR
                    IFNULL(OLD.is_primary, 0) != IFNULL(NEW.is_primary, 0)
                );
                
                SELECT id INTO current_history_id 
                FROM civicrm_address_history 
                WHERE original_address_id = NEW.id 
                AND (end_date IS NULL OR end_date > NOW())
                ORDER BY start_date DESC 
                LIMIT 1;
                
                IF significant_change THEN
                    IF current_history_id IS NOT NULL THEN
                        UPDATE civicrm_address_history 
                        SET end_date = NOW() 
                        WHERE id = current_history_id;
                    END IF;
                    
                    IF NEW.is_primary = 1 AND NEW.location_type_id IS NOT NULL THEN
                        UPDATE civicrm_address_history 
                        SET end_date = NOW() 
                        WHERE contact_id = NEW.contact_id 
                        AND location_type_id = NEW.location_type_id 
                        AND is_primary = 1 
                        AND id != IFNULL(current_history_id, 0)
                        AND (end_date IS NULL OR end_date > NOW());
                    END IF;
                    
                    INSERT INTO civicrm_address_history (
                        contact_id, location_type_id, is_primary, is_billing,
                        street_address, supplemental_address_1, supplemental_address_2,
                        city, state_province_id, postal_code, country_id,
                        start_date, original_address_id, created_date
                    ) VALUES (
                        NEW.contact_id, NEW.location_type_id, NEW.is_primary, NEW.is_billing,
                        NEW.street_address, NEW.supplemental_address_1, NEW.supplemental_address_2,
                        NEW.city, NEW.state_province_id, NEW.postal_code, NEW.country_id,
                        NOW(), NEW.id, NOW()
                    );
                ELSE
                    IF current_history_id IS NOT NULL THEN
                        UPDATE civicrm_address_history SET
                            location_type_id = NEW.location_type_id,
                            is_primary = NEW.is_primary,
                            is_billing = NEW.is_billing,
                            street_address = NEW.street_address,
                            supplemental_address_1 = NEW.supplemental_address_1,
                            supplemental_address_2 = NEW.supplemental_address_2,
                            city = NEW.city,
                            state_province_id = NEW.state_province_id,
                            postal_code = NEW.postal_code,
                            country_id = NEW.country_id,
                            modified_date = NOW()
                        WHERE id = current_history_id;
                    END IF;
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
            IF OLD.contact_id IS NOT NULL THEN
                UPDATE civicrm_address_history 
                SET end_date = NOW() 
                WHERE original_address_id = OLD.id 
                AND (end_date IS NULL OR end_date > NOW());
            END IF;
        END
      ");
      
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message('Manual Trigger Creation Error: ' . $e->getMessage());
      throw $e;
    }
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
      try {
        CRM_Core_DAO::executeQuery("DROP TRIGGER IF EXISTS {$trigger}");
      } catch (Exception $e) {
        // Ignore errors when dropping triggers
        CRM_Core_Error::debug_log_message("Could not drop trigger {$trigger}: " . $e->getMessage());
      }
    }
  }
}
