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
 * Implements hook_civicrm_entityTypes().
 */
function addresshistory_civicrm_entityTypes(&$entityTypes) {
  _addresshistory_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_triggerInfo().
 */
function addresshistory_civicrm_triggerInfo(&$info, $tableName = NULL) {
  // Only add our triggers if we're looking at all tables or specifically the address table
  if ($tableName === NULL || $tableName === 'civicrm_address') {
    
    // INSERT trigger
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

    // UPDATE trigger
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

    // DELETE trigger
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
 * Implements hook_civicrm_tabset().
 */
function addresshistory_civicrm_tabset($tabsetName, &$tabs, $context) {
  // Only add tab to contact view pages and only if we have a valid contact ID
  if ($tabsetName !== 'civicrm/contact/view' || empty($context['contact_id'])) {
    return;
  }
  
  $contactId = $context['contact_id'];
  
  // Validate contact ID and permissions
  if (!is_numeric($contactId) || $contactId <= 0 || !CRM_Contact_BAO_Contact_Permission::allow($contactId)) {
    return;
  }
  
  // Get count safely
  $count = 0;
  try {
    $count = CRM_Addresshistory_BAO_AddressHistory::getAddressHistoryCount($contactId);
  } catch (Exception $e) {
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
 * Implements hook_civicrm_contactSummaryTabs().
 */
function addresshistory_civicrm_contactSummaryTabs(&$tabs) {
  // Check if Contact Layout Editor is enabled
  try {
    $manager = CRM_Extension_System::singleton()->getManager();
    $hasContactLayoutEditor = (
      $manager->getStatus('org.civicrm.contactlayout') === CRM_Extension_Manager::STATUS_INSTALLED ||
      $manager->getStatus('uk.co.vedaconsulting.contactlayout') === CRM_Extension_Manager::STATUS_INSTALLED
    );
    
    if (!$hasContactLayoutEditor) {
      return;
    }
  } catch (Exception $e) {
    return;
  }
  
  $tabs['address_history'] = [
    'id' => 'address_history',
    'title' => E::ts('Address History'),
    'weight' => 300,
    'icon' => 'fa-history',
    'is_active' => TRUE,
  ];
}

/**
 * Implements hook_civicrm_buildForm().
 * 
 * Modifies the merge form to show address history information via JavaScript injection.
 */
function addresshistory_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Merge') {
    // Get contact IDs from the form
    $cid = $form->getVar('_cid');
    $oid = $form->getVar('_oid');
    
    if ($cid && $oid) {
      // Get address history counts for both contacts
      $mainCount = CRM_Addresshistory_BAO_AddressHistory::getAddressHistoryCount($cid);
      $otherCount = CRM_Addresshistory_BAO_AddressHistory::getAddressHistoryCount($oid);
      
      // Only show if there are address history records
      if ($mainCount > 0 || $otherCount > 0) {
        // Add JavaScript and CSS to inject the address history section
        CRM_Core_Resources::singleton()->addScript("
          CRM.$(function($) {
            // Wait for the page to be ready
            setTimeout(function() {
              // Find the location to insert our section (after other merge options)
              var insertLocation = null;
              
              // Try to find existing merge tables or accordion sections
              if ($('.crm-contact-merge-form-block table.form-layout-compressed').length > 0) {
                insertLocation = $('.crm-contact-merge-form-block table.form-layout-compressed').last();
              } else if ($('#Contact').length > 0) {
                insertLocation = $('#Contact').parent();
              } else if ($('.crm-contact-merge-form-block').length > 0) {
                insertLocation = $('.crm-contact-merge-form-block').last();
              }
              
              if (insertLocation) {
                var addressHistorySection = $('<div class=\"crm-accordion-wrapper crm-address-history-accordion\">' +
                  '<div class=\"crm-accordion-header\">' +
                    '<span class=\"crm-accordion-pointer\"></span>' +
                    '" . ts('Address History') . " ' +
                    '<span style=\"font-weight: normal; color: #666;\">' +
                      '(" . ts('Main: %1, Other: %2', [1 => $mainCount, 2 => $otherCount]) . ")' +
                    '</span>' +
                  '</div>' +
                  '<div class=\"crm-accordion-body\" style=\"display: block;\">' +
                    '<div class=\"crm-block crm-form-block\">' +
                      '<table class=\"form-layout-compressed\">' +
                        '<tr>' +
                          '<td class=\"label\" style=\"width: 20%;\">" . ts('Records') . "</td>' +
                          '<td>' +
                            '<div style=\"margin-bottom: 10px;\">' +
                              '<strong>" . ts('Main Contact') . ":</strong> {$mainCount} " . ts('address history records') . "<br>' +
                              '<strong>" . ts('Other Contact') . ":</strong> {$otherCount} " . ts('address history records') . "' +
                            '</div>' +
                          '</td>' +
                        '</tr>' +
                        '<tr>' +
                          '<td class=\"label\">" . ts('Action') . "</td>' +
                          '<td>' +
                            '<div style=\"margin: 5px 0;\">' +
                              '<label style=\"font-weight: normal;\">' +
                                '<input type=\"radio\" name=\"address_history_action\" value=\"move\" checked style=\"margin-right: 5px;\">' +
                                '<strong>" . ts('Move all address history to main contact') . "</strong>' +
                                '<div style=\"color: #666; font-size: 0.9em; margin-left: 20px;\">" . ts('Recommended: Combines all address history into one complete timeline') . "</div>' +
                              '</label>' +
                            '</div>' +
                            '<div style=\"margin: 5px 0;\">' +
                              '<label style=\"font-weight: normal;\">' +
                                '<input type=\"radio\" name=\"address_history_action\" value=\"copy\" style=\"margin-right: 5px;\">' +
                                '<strong>" . ts('Copy address history to main contact') . "</strong>' +
                                '<div style=\"color: #666; font-size: 0.9em; margin-left: 20px;\">" . ts('Creates duplicates - use only if you need to preserve original records') . "</div>' +
                              '</label>' +
                            '</div>' +
                            '<div style=\"margin: 5px 0;\">' +
                              '<label style=\"font-weight: normal;\">' +
                                '<input type=\"radio\" name=\"address_history_action\" value=\"keep\" style=\"margin-right: 5px;\">' +
                                '<strong>" . ts('Keep address histories separate') . "</strong>' +
                                '<div style=\"color: #666; font-size: 0.9em; margin-left: 20px;\">" . ts('Address history from other contact will be deleted with the contact') . "</div>' +
                              '</label>' +
                            '</div>' +
                          '</td>' +
                        '</tr>' +
                      '</table>' +
                    '</div>' +
                  '</div>' +
                '</div>');
                
                // Insert after the last merge section
                insertLocation.after(addressHistorySection);
                
                // Make it collapsible like other CiviCRM accordions
                addressHistorySection.find('.crm-accordion-header').on('click', function() {
                  var body = $(this).next('.crm-accordion-body');
                  var wrapper = $(this).parent();
                  
                  if (body.is(':visible')) {
                    body.slideUp();
                    wrapper.addClass('collapsed');
                  } else {
                    body.slideDown();
                    wrapper.removeClass('collapsed');
                  }
                });
                
                // Handle form submission to capture the selected action
                $('form#Merge').on('submit', function() {
                  var selectedAction = $('input[name=\"address_history_action\"]:checked').val();
                  
                  // Add hidden field to pass the action to the backend
                  if (selectedAction && !$('input[name=\"_address_history_action\"]').length) {
                    $(this).append('<input type=\"hidden\" name=\"_address_history_action\" value=\"' + selectedAction + '\">');
                  }
                });
              }
            }, 500); // Small delay to ensure page is fully loaded
          });
        ");
        
        // Add some CSS to style the accordion properly
        CRM_Core_Resources::singleton()->addStyle("
          .crm-address-history-accordion {
            border: 1px solid #ddd;
            margin: 10px 0;
            background: #f8f9fa;
          }
          .crm-address-history-accordion .crm-accordion-header {
            background: #e9ecef;
            padding: 10px 15px;
            cursor: pointer;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
          }
          .crm-address-history-accordion .crm-accordion-pointer:before {
            content: '▼';
            margin-right: 8px;
            font-size: 10px;
          }
          .crm-address-history-accordion.collapsed .crm-accordion-pointer:before {
            content: '▶';
          }
          .crm-address-history-accordion .crm-accordion-body {
            padding: 15px;
          }
        ");
      }
    }
  }
}

/**
 * Enhanced merge processing - captures the user's choice
 */
function addresshistory_civicrm_merge($type, &$data, $mainId = NULL, $otherId = NULL, $tables = NULL) {
  if ($type == 'batch' && $mainId && $otherId) {
    try {
      // Get the selected action from the form submission
      $action = CRM_Utils_Request::retrieve('_address_history_action', 'String');
      if (!$action) {
        $action = 'move'; // Default behavior
      }
      
      CRM_Addresshistory_BAO_AddressHistory::mergeAddressHistory($mainId, $otherId, ['action' => $action]);
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
