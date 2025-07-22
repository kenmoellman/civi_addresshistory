<?php
require_once 'addresshistory.civix.php';

use CRM_Addresshistory_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 */
function addresshistory_civicrm_config(&$config) {
  _addresshistory_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 */
function addresshistory_civicrm_xmlMenu(&$files) {
  _addresshistory_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 */
function addresshistory_civicrm_install() {
  _addresshistory_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 */
function addresshistory_civicrm_postInstall() {
  _addresshistory_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function addresshistory_civicrm_uninstall() {
  _addresshistory_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 */
function addresshistory_civicrm_enable() {
  _addresshistory_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 */
function addresshistory_civicrm_disable() {
  _addresshistory_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 */
function addresshistory_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _addresshistory_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 */
function addresshistory_civicrm_managed(&$entities) {
  _addresshistory_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 */
function addresshistory_civicrm_caseTypes(&$caseTypes) {
  _addresshistory_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 */
function addresshistory_civicrm_angularModules(&$angularModules) {
  _addresshistory_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 */
function addresshistory_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _addresshistory_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 */
function addresshistory_civicrm_entityTypes(&$entityTypes) {
  _addresshistory_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_tabset().
 */
function addresshistory_civicrm_tabset($tabsetName, &$tabs, $context) {
  // Temporarily disable to isolate the issue
  return;
  
  // DEBUG: Log what's being called
  CRM_Core_Error::debug_log_message("Tabset called - Name: {$tabsetName}, Context: " . print_r($context, true));
  
  // Only add tab to contact view pages and only if we have a valid contact ID
  if ($tabsetName !== 'civicrm/contact/view' || empty($context['contact_id'])) {
    return;
  }
  
  $contactId = $context['contact_id'];
  
  // Validate contact ID and permissions
  if (!is_numeric($contactId) || $contactId <= 0) {
    return;
  }
  
  if (!CRM_Contact_BAO_Contact_Permission::allow($contactId)) {
    return;
  }
  
  // Get count safely
  $count = 0;
  try {
    $count = CRM_Addresshistory_BAO_AddressHistory::getAddressHistoryCount($contactId);
  } catch (Exception $e) {
    CRM_Core_Error::debug_log_message('Address History Tab Count Error: ' . $e->getMessage());
    // Continue with count = 0
  }
  
  // Add the tab
  $tabs['address_history'] = [
    'id' => 'address_history',
    'url' => CRM_Utils_System::url('civicrm/contact/view/address-history', [
      'cid' => $contactId,
      'reset' => 1,
    ]),
    'title' => E::ts('Address History'),
    'weight' => 300,
    'count' => $count,
    'class' => 'livePage',
  ];
}

/**
 * Implements hook_civicrm_merge().
 */
function addresshistory_civicrm_merge($type, &$data, $mainId = NULL, $otherId = NULL, $tables = NULL) {
  if ($type == 'batch' && $mainId && $otherId) {
    try {
      CRM_Addresshistory_BAO_AddressHistory::mergeAddressHistory($mainId, $otherId);
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message('Address History Merge Error: ' . $e->getMessage());
    }
  }
}

/**
 * Implements hook_civicrm_triggerInfo().
 * 
 * This makes our triggers part of CiviCRM's trigger rebuild system.
 */
function addresshistory_civicrm_triggerInfo(&$info, $tableName = NULL) {
  // Only add our triggers if we're looking at all tables or specifically the address table
  if ($tableName === NULL || $tableName === 'civicrm_address') {
    
    // Define our custom triggers - simplified to avoid DECLARE issues
    $info[] = [
      'table' => 'civicrm_address',
      'when' => 'AFTER',
      'event' => 'INSERT',
      'sql' => "
        IF NEW.contact_id IS NOT NULL THEN
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
      ",
    ];

    $info[] = [
      'table' => 'civicrm_address',
      'when' => 'AFTER', 
      'event' => 'UPDATE',
      'sql' => "
        IF NEW.contact_id IS NOT NULL THEN
          IF (IFNULL(OLD.street_address, '') != IFNULL(NEW.street_address, '') OR
              IFNULL(OLD.city, '') != IFNULL(NEW.city, '') OR
              IFNULL(OLD.postal_code, '') != IFNULL(NEW.postal_code, '') OR
              IFNULL(OLD.state_province_id, 0) != IFNULL(NEW.state_province_id, 0) OR
              IFNULL(OLD.country_id, 0) != IFNULL(NEW.country_id, 0) OR
              IFNULL(OLD.location_type_id, 0) != IFNULL(NEW.location_type_id, 0) OR
              IFNULL(OLD.is_primary, 0) != IFNULL(NEW.is_primary, 0)) THEN
            
            UPDATE civicrm_address_history 
            SET end_date = NOW() 
            WHERE original_address_id = NEW.id 
            AND (end_date IS NULL OR end_date > NOW());
            
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
            WHERE original_address_id = NEW.id 
            AND (end_date IS NULL OR end_date > NOW());
          END IF;
        END IF;
      ",
    ];

    $info[] = [
      'table' => 'civicrm_address',
      'when' => 'AFTER',
      'event' => 'DELETE', 
      'sql' => "
        IF OLD.contact_id IS NOT NULL THEN
          UPDATE civicrm_address_history 
          SET end_date = NOW() 
          WHERE original_address_id = OLD.id 
          AND (end_date IS NULL OR end_date > NOW());
        END IF;
      ",
    ];
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 */
function addresshistory_civicrm_navigationMenu(&$menu) {
  // Re-enabled with safety method in upgrader
  _addresshistory_civix_civicrm_navigationMenu($menu);
}
