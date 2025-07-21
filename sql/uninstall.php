-- Uninstall script for address history extension
-- File: sql/uninstall.sql

-- Drop triggers first
DROP TRIGGER IF EXISTS civicrm_address_history_after_insert;
DROP TRIGGER IF EXISTS civicrm_address_history_after_update;
DROP TRIGGER IF EXISTS civicrm_address_history_after_delete;

-- Drop the address history table
DROP TABLE IF EXISTS `civicrm_address_history`;
