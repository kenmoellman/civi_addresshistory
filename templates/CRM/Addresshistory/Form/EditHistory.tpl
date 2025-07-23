{* File: templates/CRM/Addresshistory/Form/EditHistory.tpl *}

<div class="crm-content-block">
  <div id="help">
    <p>{ts}Edit the start and end dates for this address history record.{/ts}</p>
    
    <div class="crm-section address-summary-section">
      <div class="label"><strong>{ts}Location Type:{/ts}</strong></div>
      <div class="content">{$locationTypeName}</div>
      <div class="clear"></div>
    </div>
    
    <div class="crm-section address-summary-section">
      <div class="label"><strong>{ts}Address:{/ts}</strong></div>
      <div class="content">{$addressSummary}</div>
      <div class="clear"></div>
    </div>
    
    <div class="crm-section address-summary-section">
      <div class="label"><strong>{ts}Primary Address:{/ts}</strong></div>
      <div class="content">
        {if $isPrimary}
          <span class="crm-marker crm-marker-primary">
            <i class="crm-i fa-check-circle"></i> {ts}Yes{/ts}
          </span>
        {else}
          <span class="crm-marker crm-marker-secondary">
            <i class="crm-i fa-circle-o"></i> {ts}No{/ts}
          </span>
        {/if}
      </div>
      <div class="clear"></div>
    </div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.start_date.label}</div>
    <div class="content">
      {$form.start_date.html}
      <div class="description">{ts}When this address became active for the contact.{/ts}</div>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.end_date_type.label}</div>
    <div class="content">
      {$form.end_date_type.html}
      <div class="description">{ts}Choose whether this address has an end date or is still current.{/ts}</div>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section" id="specific-end-date-section">
    <div class="label">{$form.end_date_value.label}</div>
    <div class="content">
      {$form.end_date_value.html}
      <div class="description">{ts}When this address stopped being active.{/ts}</div>
    </div>
    <div class="clear"></div>
  </div>

  <div class="messages warning">
    <div class="icon warn-icon"></div>
    <strong>{ts}Warning:{/ts}</strong> {ts}Changing these dates may affect the integrity of the address history timeline. Please ensure the dates are accurate and do not overlap with other address history records for the same location type.{/ts}
  </div>

</div>

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  var formSubmitted = false;
  
  // Function to show/hide the end date picker based on radio selection
  function toggleEndDatePicker() {
    var endDateType = $('input[name="end_date_type"]:checked').val();
    console.log('Address History Edit: End date type changed to:', endDateType);
    
    if (endDateType === 'specific') {
      $('#specific-end-date-section').show();
      $('#end_date_value').focus();
    } else {
      $('#specific-end-date-section').hide();
      $('#end_date_value').val(''); // Clear the date when hiding
    }
  }
  
  // Initialize the display based on current selection
  toggleEndDatePicker();
  
  // Handle radio button changes
  $('input[name="end_date_type"]').on('change', function() {
    toggleEndDatePicker();
  });
  
  // Track when the form is actually submitted
  $('form.CRM_Addresshistory_Form_EditHistory').on('submit', function() {
    formSubmitted = true;
    
    // Log form values before submission for debugging
    var startDate = $('#start_date').val();
    var endDateType = $('input[name="end_date_type"]:checked').val();
    var endDateValue = $('#end_date_value').val();
    
    console.log('Address History Edit: Form submission details:');
    console.log('  - start_date: "' + startDate + '"');
    console.log('  - end_date_type: "' + endDateType + '"');
    console.log('  - end_date_value: "' + endDateValue + '"');
    console.log('  - end_date_section_visible: ' + $('#specific-end-date-section').is(':visible'));
    
    // Also log all form data being submitted
    var formData = $(this).serialize();
    console.log('Address History Edit: Serialized form data: ' + formData);
    
    // Start monitoring for success only AFTER form submission
    var checkInterval = setInterval(function() {
      // Check for success messages
      if ($('.crm-status-box-outer .status.success').length > 0 && 
          $('.crm-status-box-outer .status.success:contains("successfully")').length > 0) {
        
        clearInterval(checkInterval);
        
        // Close the popup after successful save
        setTimeout(function() {
          $('.ui-dialog-content:visible').dialog('close');
          
          // Force refresh of address history content
          if (window.parent && window.parent.$) {
            // Try multiple ways to refresh the address history
            if (window.parent.$('#tab_address_history').length > 0) {
              // We're in a tab context - refresh the tab
              window.parent.$('#tab_address_history').crmSnippet('refresh');
            } else if (window.parent.$('.crm-results-block').length > 0) {
              // We're on the address history page - reload it
              window.parent.location.reload();
            } else {
              // Fallback - just reload the parent
              window.parent.location.reload();
            }
          } else {
            // No parent window, reload current window
            window.location.reload();
          }
        }, 500);
      }
      
      // Also check if we've been redirected to the address history page
      else if ($('h3:contains("Address History")').length > 0 || 
               $('.crm-results-block-header:contains("Address History")').length > 0) {
        
        clearInterval(checkInterval);
        
        setTimeout(function() {
          $('.ui-dialog-content:visible').dialog('close');
          
          if (window.parent && window.parent.$) {
            if (window.parent.$('#tab_address_history').length > 0) {
              window.parent.$('#tab_address_history').crmSnippet('refresh');
            } else {
              window.parent.location.reload();
            }
          }
        }, 500);
      }
    }, 500);
    
    // Stop checking after 10 seconds
    setTimeout(function() {
      clearInterval(checkInterval);
    }, 10000);
  });
  
  // Handle cancel button
  $('input[name="_qf_EditHistory_cancel"]').on('click', function(e) {
    if ($(this).closest('.ui-dialog').length > 0) {
      e.preventDefault();
      $('.ui-dialog-content').dialog('close');
      return false;
    }
  });
});
</script>
{/literal}

{literal}
<style>
.address-summary-section {
  margin-bottom: 10px;
  padding: 8px;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.address-summary-section .label {
  width: 120px;
  display: inline-block;
  vertical-align: top;
}

.address-summary-section .content {
  display: inline-block;
  width: calc(100% - 130px);
  margin-left: 10px;
}

.crm-marker {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.crm-marker-primary {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.crm-marker-secondary {
  background-color: #f8f9fa;
  color: #6c757d;
  border: 1px solid #dee2e6;
}

.crm-marker i {
  margin-right: 4px;
}

#specific-end-date-section {
  margin-left: 20px;
  padding: 10px;
  background-color: #f9f9f9;
  border-left: 3px solid #007cba;
  border-radius: 4px;
}

.crm-section input[type="radio"] {
  margin-right: 8px;
}

.crm-section .content label {
  font-weight: normal;
  margin-right: 15px;
}
</style>
{/literal}
