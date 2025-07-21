-- Uninstall script for address history extension
-- File: sql/uninstall.sql

-- Drop triggers (will be done by upgrader)
-- Drop the address history table
DROP TABLE IF EXISTS `civicrm_address_history`;
