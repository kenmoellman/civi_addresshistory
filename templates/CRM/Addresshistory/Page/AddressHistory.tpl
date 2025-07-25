{* File: templates/CRM/Addresshistory/Page/AddressHistory.tpl *}

<div class="crm-content-block">
  <div id="help">
    {ts}This page shows the complete address history for this contact, including start and end dates for each address.{/ts}
    {if $canEdit}
      <br/>{ts}As an administrator, you can edit the start and end dates by clicking on individual history records.{/ts}
    {/if}
  </div>

  {if $addressHistory}
    <div class="crm-results-block">
      <div class="crm-results-block-header">
        <h3>{ts}Address History{/ts}</h3>
      </div>
      
      <table class="selector row-highlight">
        <thead class="sticky">
          <tr>
            <th>{ts}Location Type{/ts}</th>
            <th>{ts}Address{/ts}</th>
            <th>{ts}City, State, Postal{/ts}</th>
            <th>{ts}Country{/ts}</th>
            <th>{ts}Primary{/ts}</th>
            <th>{ts}Start Date{/ts}</th>
            <th>{ts}End Date{/ts}</th>
            <th>{ts}Status{/ts}</th>
            {if $canEdit}
              <th>{ts}Actions{/ts}</th>
            {/if}
          </tr>
        </thead>
        <tbody>
          {foreach from=$addressHistory item=address}
            <tr class="{cycle values="odd-row,even-row"}">
              <td>{$address.location_type|escape}</td>
              <td>{$address.street|escape}</td>
              <td>{$address.city_state_postal|escape}</td>
              <td>{$address.country|escape}</td>
              <td>
                {if $address.is_primary}
                  <i class="crm-i fa-check" title="{ts}Primary{/ts}"></i>
                {/if}
              </td>
              <td>
                {if $address.start_date}
                  {$address.start_date|crmDate:"%B %d, %Y"}
                {/if}
              </td>
              <td>
                {if $address.end_date}
                  {$address.end_date|crmDate:"%B %d, %Y"}
                {else}
                  <em>{ts}Current{/ts}</em>
                {/if}
              </td>
              <td>
                {if $address.end_date}
                  <span class="crm-label crm-label-inactive">{ts}Inactive{/ts}</span>
                {else}
                  <span class="crm-label crm-label-active">{ts}Active{/ts}</span>
                {/if}
              </td>
              {if $canEdit}
                <td>
                  <a href="{crmURL p='civicrm/contact/view/address-history/edit' q="reset=1&cid=`$contactId`&id=`$address.id`"}" 
                     class="action-item crm-hover-button crm-popup" 
                     title="{ts}Edit History Record{/ts}">
                    <i class="crm-i fa-pencil"></i> {ts}Edit{/ts}
                  </a>
                  <a href="{crmURL p='civicrm/contact/view/address-history/delete' q="reset=1&cid=`$contactId`&id=`$address.id`"}" 
                     class="action-item crm-hover-button crm-popup delete-action" 
                     title="{ts}Delete History Record{/ts}">
                    <i class="crm-i fa-trash"></i> {ts}Delete{/ts}
                  </a>
                </td>
              {/if}
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      {ts}No address history found for this contact.{/ts}
    </div>
  {/if}
</div>

{* Only show back button when not in a tab context *}
<div class="crm-submit-buttons" id="address-history-back-button">
  <a class="button" href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$contactId`"}">
    <i class="crm-i fa-chevron-left"></i> {ts}Back to Contact Summary{/ts}
  </a>
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  // Hide back button if we're in a tab context
  if (window.location.hash.indexOf('address_history') !== -1 || 
      $('#tab_address_history').length > 0 ||
      $('body').hasClass('crm-contact-view')) {
    $('#address-history-back-button').hide();
  }
  
  // Enhanced refresh handling for address history tab
  $(document).on('crmFormSuccess', function(e) {
    setTimeout(function() {
      if ($('#tab_address_history').length > 0) {
        // We're in a tab, refresh the tab content
        $('#tab_address_history').crmSnippet('refresh');
      } else {
        // We're on a standalone page, reload
        window.location.reload();
      }
    }, 1000); // Wait a bit longer to ensure the update is committed
  });
  
  // Also listen for when dialogs close after form success
  $(document).on('dialogclose', '.ui-dialog', function() {
    // Check if this was an address history form dialog
    if ($(this).find('form.CRM_Addresshistory_Form_EditHistory, form.CRM_Addresshistory_Form_DeleteHistory').length > 0) {
      setTimeout(function() {
        if ($('#tab_address_history').length > 0) {
          $('#tab_address_history').crmSnippet('refresh');
        } else {
          window.location.reload();
        }
      }, 500);
    }
  });
});
</script>
{/literal}

{literal}
<style>
.crm-label {
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
}

.crm-label-active {
  background-color: #5cb85c;
  color: white;
}

.crm-label-inactive {
  background-color: #d9534f;
  color: white;
}

.selector th {
  font-weight: bold;
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.selector td {
  padding: 8px;
  border-bottom: 1px solid #dee2e6;
}

.odd-row {
  background-color: #f9f9f9;
}

.even-row {
  background-color: white;
}

.selector tr:hover {
  background-color: #e8f4f8;
}

.delete-action {
  color: #d9534f !important;
  margin-left: 8px;
}

.delete-action:hover {
  color: #c9302c !important;
  background-color: #f2dede !important;
}
</style>
{/literal}
