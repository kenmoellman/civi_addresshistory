<?php
// File: CRM/Addresshistory/Page/AddressHistory.php

class CRM_Addresshistory_Page_AddressHistory extends CRM_Core_Page {

  /**
   * The contact ID for this address history
   *
   * @var int
   */
  protected $_contactId;

  /**
   * The contact display name
   *
   * @var string
   */
  protected $_contactDisplayName;

  /**
   * Run the page.
   *
   * This method is called after the page is created.
   */
  public function run() {
    // Get the contact ID
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    
    if (!$this->_contactId) {
      CRM_Core_Error::statusBounce('Contact ID is required');
    }
    
    // Check permission
    if (!CRM_Contact_BAO_Contact_Permission::allow($this->_contactId)) {
      CRM_Core_Error::statusBounce('You do not have permission to view this contact');
    }
    
    // Check if user can edit address history (administrators only)
    $canEdit = CRM_Core_Permission::check('administer CiviCRM');
    
    // Get contact display name
    $this->_contactDisplayName = CRM_Contact_BAO_Contact::displayName($this->_contactId);
    
    // Set page title
    CRM_Utils_System::setTitle(ts('Address History for %1', [1 => $this->_contactDisplayName]));
    
    // Get address history
    $addressHistory = CRM_Addresshistory_BAO_AddressHistory::getFormattedAddressHistory($this->_contactId);
    
    // Assign variables to template
    $this->assign('contactId', $this->_contactId);
    $this->assign('contactDisplayName', $this->_contactDisplayName);
    $this->assign('addressHistory', $addressHistory);
    $this->assign('canEdit', $canEdit);
    
    // Add breadcrumb
    $breadcrumb = [
      'title' => ts('Contact Summary'),
      'url' => CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $this->_contactId]),
    ];
    CRM_Utils_System::appendBreadCrumb([$breadcrumb]);
    
    parent::run();
  }

  /**
   * Get the contact ID
   *
   * @return int
   */
  public function getContactId() {
    return $this->_contactId;
  }

  /**
   * Format date for display
   *
   * @param string $date
   * @return string
   */
  public static function formatDate($date) {
    if (empty($date)) {
      return '';
    }
    return CRM_Utils_Date::customFormat($date, '%B %d, %Y');
  }

}
