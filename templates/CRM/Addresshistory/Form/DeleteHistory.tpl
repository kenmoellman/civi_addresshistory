{* File: templates/CRM/Addresshistory/Form/DeleteHistory.tpl *}

<div class="crm-content-block">
  <div class="messages warning">
    <div class="icon warn-icon"></div>
    <strong>{ts}Warning:{/ts}</strong> {ts}You are about to permanently delete this address history record. This action cannot be undone.{/ts}
  </div>

  <div id="help">
    <p>{ts}Are you sure you want to delete this address history record?{/ts}</p>
    
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
    
    <div class="crm-section address-summary-section">
      <div class="label"><strong>{ts}Date Range:{/ts}</strong></div>
      <div class="content">
        {if $startDate}
          {$startDate|crmDate:"%B %d, %Y"}
        {else}
          {ts}Unknown start date{/ts}
        {/if}
        {ts}to{/ts}
        {if $endDate}
          {$endDate|crmDate:"%B %d, %Y"}
        {else}
          <em>{ts}Current{/ts}</em>
        {/if}
      </div>
      <div class="clear"></div>
    </div>
  </div>

  <div class="messages status">
    <div class="icon inform-icon"></div>
    {ts}Deleting this record will permanently remove it from the address history. The actual contact address (if it still exists) will not be affected.{/ts}
  </div>

</div>

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  var formSubmitted = false;
  
  // Track when the form is actually submitted
  $('form.CRM_Addresshistory_Form_DeleteHistory').on('submit', function() {
    formSubmitted = true;
    
    // Start monitoring for success only AFTER form submission
    var checkInterval = setInterval(function() {
      // Check for success messages
      if ($('.crm-status-box-outer .status.success').length > 0 && 
          $('.crm-status-box-outer .status.success:contains("successfully")').length > 0) {
        
        clearInterval(checkInterval);
        
        // Close the popup after successful delete
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
        }, 500); // Give a bit more time for the success message to be processed
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
  $('input[name="_qf_DeleteHistory_cancel"]').on('click', function(e) {
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
</style>
{/literal}
