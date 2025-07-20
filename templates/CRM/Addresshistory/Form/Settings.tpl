{* File: templates/CRM/Addresshistory/Form/Settings.tpl *}

<div class="crm-content-block">

  <div id="help">
    <p>{ts}Configure how the Address History extension tracks address changes for contacts.{/ts}</p>
    
    {if $triggersEnabled}
      <div class="messages status">
        <div class="icon inform-icon"></div>
        <strong>{ts}Current Status:{/ts}</strong> {ts}Database triggers are currently <strong>enabled</strong> and actively tracking address changes.{/ts}
      </div>
    {else}
      <div class="messages status">
        <div class="icon inform-icon"></div>
        <strong>{ts}Current Status:{/ts}</strong> {ts}Database triggers are currently <strong>disabled</strong>. Address tracking is using CiviCRM hooks.{/ts}
      </div>
    {/if}
  </div>

  <div class="crm-section">
    <div class="label">{$form.tracking_method.label}</div>
    <div class="content">
      {$form.tracking_method.html}
      <div class="description">
        <h4>{ts}Comparison of Tracking Methods:{/ts}</h4>
        <table class="form-layout-compressed">
          <tr>
            <th></th>
            <th>{ts}Database Triggers{/ts}</th>
            <th>{ts}CiviCRM Hooks{/ts}</th>
          </tr>
          <tr>
            <td><strong>{ts}Coverage{/ts}</strong></td>
            <td><span style="color: green;">✓</span> {ts}Captures ALL changes{/ts}</td>
            <td><span style="color: orange;">~</span> {ts}CiviCRM interfaces only{/ts}</td>
          </tr>
          <tr>
            <td><strong>{ts}Reliability{/ts}</strong></td>
            <td><span style="color: green;">✓</span> {ts}Cannot be bypassed{/ts}</td>
            <td><span style="color: orange;">~</span> {ts}Can be bypassed{/ts}</td>
          </tr>
          <tr>
            <td><strong>{ts}Performance{/ts}</strong></td>
            <td><span style="color: green;">✓</span> {ts}Database level{/ts}</td>
            <td><span style="color: orange;">~</span> {ts}PHP processing{/ts}</td>
          </tr>
          <tr>
            <td><strong>{ts}Debugging{/ts}</strong></td>
            <td><span style="color: red;">✗</span> {ts}Harder to debug{/ts}</td>
            <td><span style="color: green;">✓</span> {ts}Easier to debug{/ts}</td>
          </tr>
          <tr>
            <td><strong>{ts}Portability{/ts}</strong></td>
            <td><span style="color: red;">✗</span> {ts}Database specific{/ts}</td>
            <td><span style="color: green;">✓</span> {ts}Platform independent{/ts}</td>
          </tr>
        </table>
      </div>
    </div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="content">
      <h4>{ts}Important Notes:{/ts}</h4>
      <ul>
        <li>{ts}<strong>Database Triggers</strong> are recommended for production environments where data integrity is critical.{/ts}</li>
        <li>{ts}<strong>CiviCRM Hooks</strong> may be preferred for development environments or when you need easier debugging.{/ts}</li>
        <li>{ts}Switching between methods will not affect existing address history data.{/ts}</li>
        <li>{ts}Both methods can handle contact merging properly.{/ts}</li>
        <li>{ts}If you're unsure, start with Database Triggers - you can always switch back.{/ts}</li>
      </ul>
    </div>
  </div>

</div>

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<style>
.form-layout-compressed th {
  background-color: #f8f9fa;
  padding: 8px;
  border: 1px solid #dee2e6;
  font-weight: bold;
}

.form-layout-compressed td {
  padding: 6px 8px;
  border: 1px solid #dee2e6;
}

.form-layout-compressed tr:nth-child(even) {
  background-color: #f8f9fa;
}

.messages.status {
  margin-bottom: 20px;
  padding: 10px;
  border-left: 4px solid #2196F3;
  background-color: #e3f2fd;
}
</style>
{/literal}
