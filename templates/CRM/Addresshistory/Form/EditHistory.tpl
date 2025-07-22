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
    <div class="label">{$form.end_date.label}</div>
    <div class="content">
      {$form.end_date.html}
      <div class="description">{ts}When this address stopped being active. Leave blank if this is still the current address.{/ts}</div>
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
  // Handle form submission in popup context
  $('form.{/literal}{$form.formClass}{literal}').on('submit', function() {
    // Check if we're in a popup (dialog) context
    if ($(this).closest('.ui-dialog').length > 0) {
      // We're in a popup, the postProcess will handle closing
      return true;
    }
  });
  
  // Handle cancel button in popup context
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
</style>
{/literal}
