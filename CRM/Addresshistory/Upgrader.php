<?php
// File: CRM/Addresshistory/Upgrader.php

use CRM_Addresshistory_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Addresshistory_Upgrader extends CRM_Addresshistory_Upgrader_Base {

  /**
   * Called during installation.
   */
  public function install() {
    $this->executeSqlFile('sql/install.sql');
    // Force trigger rebuild to include our triggers
    CRM_Core_DAO::triggerRebuild();
  }

  /**
   * Called after installation.
   */
  public function postInstall() {
    // Populate existing addresses into history table
    CRM_Addresshistory_BAO_AddressHistory::populateExistingAddresses();
  }

  /**
   * Called during uninstall.
   */
  public function uninstall() {
    // Triggers will be removed automatically when we rebuild without our extension
    $this->executeSqlFile('sql/uninstall.sql');
  }

  /**
   * Called during enable.
   */
  public function enable() {
    // Force CiviCRM to rebuild triggers which will include our triggerInfo hook
    CRM_Core_DAO::triggerRebuild();
    
    // Also try to populate existing addresses if table is empty
    $count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM civicrm_address_history");
    if ($count == 0) {
      CRM_Addresshistory_BAO_AddressHistory::populateExistingAddresses();
    }
  }

  /**
   * Called during disable.
   */
  public function disable() {
    // Rebuild triggers without our extension (this will remove our triggers)
    CRM_Core_DAO::triggerRebuild();
  }

  /**
   * Append navigation menu items (required by civix).
   */
  public function appendNavigationMenu(&$menu) {
    // We don't need to add any navigation menu items
    // This method exists to prevent civix from breaking
    return;
  }

  /** Template override system for merging **/

public function createTemplateOverride() {
  // Create custom template directory in your extension
  $templateDir = E::path('templates/CRM/Contact/Form');
  if (!is_dir($templateDir)) {
    mkdir($templateDir, 0755, true);
  }
  
  $mergeTemplate = $templateDir . '/Merge.tpl';
  
  // Only create if it doesn't exist
  if (!file_exists($mergeTemplate)) {
    // Get the original template
    $originalTemplate = CRM_Core_Config::singleton()->templateDir[0] . '/CRM/Contact/Form/Merge.tpl';
    
    if (file_exists($originalTemplate)) {
      // Copy original template
      copy($originalTemplate, $mergeTemplate);
      
      // Add our address history section
      $content = file_get_contents($mergeTemplate);
      
      // Find the location to insert our content (before the submit buttons)
      $insertPoint = '{include file="CRM/common/formButtons.tpl" location="bottom"}';
      
      $addressHistorySection = '
{* Address History Extension - Start *}
{if $addressHistoryMainCount || $addressHistoryOtherCount}
<div class="crm-accordion-wrapper collapsed">
  <div class="crm-accordion-header">
    {ts}Address History{/ts} 
    <span style="font-weight: normal; color: #666;">
      ({ts 1=$addressHistoryMainCount}Main: %1{/ts}, {ts 1=$addressHistoryOtherCount}Other: %1{/ts})
    </span>
  </div>
  <div class="crm-accordion-body">
    <div class="crm-block crm-form-block">
      <table class="form-layout-compressed">
        <tr>
          <td class="label">{ts}Address History Records{/ts}</td>
          <td>
            <div class="description">
              <strong>{ts}Main Contact{/ts}:</strong> {$addressHistoryMainCount} {ts}records{/ts}<br>
              <strong>{ts}Other Contact{/ts}:</strong> {$addressHistoryOtherCount} {ts}records{/ts}
            </div>
          </td>
        </tr>
        <tr>
          <td class="label">{ts}Action{/ts}</td>
          <td>
            <div class="radio">
              <input type="radio" name="address_history_action" value="move" id="address_history_move" checked="checked">
              <label for="address_history_move">
                <strong>{ts}Move all address history to main contact{/ts}</strong>
                <div class="description">{ts}Recommended: Combines all address history into one complete timeline{/ts}</div>
              </label>
            </div>
            <div class="radio">
              <input type="radio" name="address_history_action" value="copy" id="address_history_copy">
              <label for="address_history_copy">
                <strong>{ts}Copy address history to main contact{/ts}</strong>
                <div class="description">{ts}Creates duplicates - use only if you need to preserve original records{/ts}</div>
              </label>
            </div>
            <div class="radio">
              <input type="radio" name="address_history_action" value="keep" id="address_history_keep">
              <label for="address_history_keep">
                <strong>{ts}Keep address histories separate{/ts}</strong>
                <div class="description">{ts}Address history from other contact will be deleted with the contact{/ts}</div>
              </label>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
{/if}
{* Address History Extension - End *}

';
      
      // Insert our section
      $content = str_replace($insertPoint, $addressHistorySection . $insertPoint, $content);
      
      // Write the modified template
      file_put_contents($mergeTemplate, $content);
      
      CRM_Core_Session::setStatus(
        ts('Address History merge template override created successfully.'),
        ts('Template Override'),
        'success'
      );
    }
  }
}

/**
 * Method to update template override when needed
 */
public function updateTemplateOverride() {
  $this->createTemplateOverride();
}
}
