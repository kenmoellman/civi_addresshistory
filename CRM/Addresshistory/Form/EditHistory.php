<?php
// File: CRM/Addresshistory/Form/EditHistory.php

class CRM_Addresshistory_Form_EditHistory extends CRM_Core_Form {

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
      CRM_Core_Error::statusBounce('You do not have permission to edit address history');
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
    CRM_Utils_System::setTitle(ts('Edit Address History for %1', [1 => $contactDisplayName]));
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    // Add form elements
    $this->add('datepicker', 'start_date', ts('Start Date'), [], TRUE, ['time' => TRUE]);
    $this->add('datepicker', 'end_date', ts('End Date'), [], FALSE, ['time' => TRUE]);
    
    // Assign data to template
    $this->assign('addressSummary', $this->getAddressSummary());
    $this->assign('locationTypeName', $this->getLocationTypeName());
    $this->assign('isPrimary', $this->_addressHistory->is_primary);
    
    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ],
    ]);

    // Set default values
    $defaults = [
      'start_date' => $this->_addressHistory->start_date,
      'end_date' => $this->_addressHistory->end_date,
    ];
    $this->setDefaults($defaults);
  }

  /**
   * Add local and global form rules.
   */
  public function addRules() {
    $this->addFormRule(['CRM_Addresshistory_Form_EditHistory', 'formRule'], $this);
  }

  /**
   * Global validation rules for the form.
   */
  public static function formRule($values, $files, $self) {
    $errors = [];
    
    if (!empty($values['start_date']) && !empty($values['end_date'])) {
      if (strtotime($values['start_date']) >= strtotime($values['end_date'])) {
        $errors['end_date'] = ts('End date must be after start date');
      }
    }
    
    // Check for overlapping records for the same contact/location type
    if (!empty($values['start_date'])) {
      $overlaps = self::checkForOverlaps(
        $self->_contactId,
        $self->_addressHistory->location_type_id,
        $values['start_date'],
        $values['end_date'],
        $self->_id
      );
      
      if ($overlaps) {
        $errors['start_date'] = ts('This date range overlaps with another address history record');
      }
    }
    
    return $errors;
  }
  
  /**
   * Check for overlapping address history records.
   */
  public static function checkForOverlaps($contactId, $locationTypeId, $startDate, $endDate, $excludeId) {
    $query = "
      SELECT COUNT(*) FROM civicrm_address_history 
      WHERE contact_id = %1 
      AND location_type_id = %2 
      AND id != %3
      AND (
        (%4 BETWEEN start_date AND IFNULL(end_date, '9999-12-31'))
        OR (%5 BETWEEN start_date AND IFNULL(end_date, '9999-12-31'))
        OR (start_date BETWEEN %4 AND %5)
      )
    ";
    
    $params = [
      1 => [$contactId, 'Integer'],
      2 => [$locationTypeId, 'Integer'],
      3 => [$excludeId, 'Integer'],
      4 => [$startDate, 'String'],
      5 => [$endDate ?: '9999-12-31', 'String'],
    ];
    
    return CRM_Core_DAO::singleValueQuery($query, $params) > 0;
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $values = $this->exportValues();
    
    try {
      // Update the address history record
      $this->_addressHistory->start_date = $values['start_date'];
      $this->_addressHistory->end_date = $values['end_date'] ?: NULL;
      $this->_addressHistory->modified_date = date('Y-m-d H:i:s');
      $this->_addressHistory->save();
      
      CRM_Core_Session::setStatus(
        ts('Address history has been updated successfully.'),
        ts('Saved'),
        'success'
      );
      
    } catch (Exception $e) {
      CRM_Core_Session::setStatus(
        ts('Error updating address history: %1', [1 => $e->getMessage()]),
        ts('Error'),
        'error'
      );
    }
    
    // Always redirect - this will either refresh the popup content or go to the page
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
