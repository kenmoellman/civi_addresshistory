{* File: templates/CRM/Addresshistory/Form/EditHistory.tpl *}

<div class="crm-content-block">
  <div id="help">
    <p>{ts}Edit the start and end dates for this address history record.{/ts}</p>
    <p><strong>{ts}Address:{/ts}</strong> {$addressSummary}</p>
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
