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
  if ($tabsetName === 'civicrm/contact/view' && !empty($context['contact_id'])) {
    $contactId = $context['contact_id'];
    
    // Check permission
    if (CRM_Contact_BAO_Contact_Permission::allow($contactId)) {
      $count = CRM_Addresshistory_BAO_AddressHistory::getAddressHistoryCount($contactId);
      
      $tabs[] = [
        'id' => 'address_history',
        'url' => CRM_Utils_System::url('civicrm/contact/view/address-history', [
          'cid' => $contactId,
          'reset' => 1,
        ]),
        'title' => E::ts('Address History'),
        'weight' => 300,
        'count' => $count,
      ];
    }
  }
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
 * Implements hook_civicrm_navigationMenu().
 */
function addresshistory_civicrm_navigationMenu(&$menu) {
  _addresshistory_civix_civicrm_navigationMenu($menu);
}
