<?php
// File: CRM/Addresshistory/Form/DeleteHistory.php

class CRM_Addresshistory_Form_DeleteHistory extends CRM_Core_Form {

  protected $_id;
  protected $_contactId;
  protected $_addressHistory;

  /**
   * Pre-process form.
   */
  public function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    
    if (!$this->_id || !$this->_contactId) {
      CRM_Core_Error::statusBounce('Address History ID and Contact ID are required');
    }
    
    // Check permissions
    if (!CRM_Core_Permission::check('administer CiviCRM')) {
      CRM_Core_Error::statusBounce('You do not have permission to delete address history');
    }
    
    // Get the address history record
    $this->_addressHistory = new CRM_Addresshistory_DAO_AddressHistory();
    $this->_addressHistory->id = $this->_id;
    if (!$this->_addressHistory->find(TRUE)) {
      CRM_Core_Error::statusBounce('Address History record not found');
    }
    
    // Verify contact matches
    if ($this->_addressHistory->contact_id != $this->_contactId) {
      CRM_Core_Error::statusBounce('Address History record does not belong to this contact');
    }
    
    $contactDisplayName = CRM_Contact_BAO_Contact::displayName($this->_contactId);
    CRM_Utils_System::setTitle(ts('Delete Address History for %1', [1 => $contactDisplayName]));
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    // Assign data to template
    $this->assign('addressSummary', $this->getAddressSummary());
    $this->assign('locationTypeName', $this->getLocationTypeName());
    $this->assign('isPrimary', $this->_addressHistory->is_primary);
    $this->assign('startDate', $this->_addressHistory->start_date);
    $this->assign('endDate', $this->_addressHistory->end_date);
    
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Delete'),
        'isDefault' => TRUE,
        'icon' => 'fa-trash',
      ],
      [
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ],
    ]);
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    try {
      // Delete the address history record
      $this->_addressHistory->delete();
      
      CRM_Core_Session::setStatus(
        ts('Address history record has been deleted successfully.'),
        ts('Deleted'),
        'success'
      );
      
    } catch (Exception $e) {
      CRM_Core_Session::setStatus(
        ts('Error deleting address history: %1', [1 => $e->getMessage()]),
        ts('Error'),
        'error'
      );
    }
    
    // Check if this is a popup/snippet context - if so, don't redirect
    if (CRM_Utils_Request::retrieve('snippet', 'String') || 
        !empty($_REQUEST['snippet']) ||
        CRM_Utils_Array::value('HTTP_X_REQUESTED_WITH', $_SERVER) == 'XMLHttpRequest') {
      // For AJAX/popup requests, just return - let JavaScript handle the popup closing
      return;
    }
    
    // Redirect back to address history tab
    $url = CRM_Utils_System::url('civicrm/contact/view/address-history', [
      'reset' => 1,
      'cid' => $this->_contactId,
    ]);
    CRM_Utils_System::redirect($url);
  }
  
  /**
   * Get a summary of the address for display.
   */
  private function getAddressSummary() {
    $parts = [];
    if ($this->_addressHistory->street_address) {
      $parts[] = $this->_addressHistory->street_address;
    }
    if ($this->_addressHistory->city) {
      $parts[] = $this->_addressHistory->city;
    }
    if ($this->_addressHistory->state_province_id) {
      try {
        $stateProvince = civicrm_api3('StateProvince', 'getvalue', [
          'id' => $this->_addressHistory->state_province_id,
          'return' => 'name',
        ]);
        $parts[] = $stateProvince;
      } catch (Exception $e) {
        // Skip if state/province not found
      }
    }
    if ($this->_addressHistory->postal_code) {
      $parts[] = $this->_addressHistory->postal_code;
    }
    
    return implode(', ', $parts);
  }

  /**
   * Get the location type name.
   */
  private function getLocationTypeName() {
    if (!$this->_addressHistory->location_type_id) {
      return ts('(No Location Type)');
    }
    
    try {
      $locationTypeName = civicrm_api3('LocationType', 'getvalue', [
        'id' => $this->_addressHistory->location_type_id,
        'return' => 'display_name',
      ]);
      return $locationTypeName;
    } catch (Exception $e) {
      return ts('Unknown Location Type (ID: %1)', [1 => $this->_addressHistory->location_type_id]);
    }
  }

}
