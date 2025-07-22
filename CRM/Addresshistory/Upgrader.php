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
    // Force trigger rebuild to include our triggers
    CRM_Core_DAO::triggerRebuild();
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
    // Triggers will be removed automatically when we rebuild without our extension
    $this->executeSqlFile('sql/uninstall.sql');
  }

  /**
   * Called during enable.
   */
  public function enable() {
    // Force CiviCRM to rebuild triggers which will include our triggerInfo hook
    CRM_Core_DAO::triggerRebuild();
    
    // Also try to populate existing addresses if table is empty
    $count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM civicrm_address_history");
    if ($count == 0) {
      CRM_Addresshistory_BAO_AddressHistory::populateExistingAddresses();
    }
  }

  /**
   * Called during disable.
   */
  public function disable() {
    // Rebuild triggers without our extension (this will remove our triggers)
    CRM_Core_DAO::triggerRebuild();
  }

  /**
   * Append navigation menu items (required by civix).
   */
  public function appendNavigationMenu(&$menu) {
    // We don't need to add any navigation menu items
    // This method exists to prevent civix from breaking
    return;
  }
}
