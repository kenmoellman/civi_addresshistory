-- SQL Schema for civicrm_address_history table
-- This should be in sql/auto_install.sql

DROP TABLE IF EXISTS `civicrm_address_history`;

CREATE TABLE `civicrm_address_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique AddressHistory ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_location_type',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary address for this contact and location type',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing address',
  `street_address` varchar(96) DEFAULT NULL COMMENT 'Concatenation of all routable street address components',
  `street_number` int(10) unsigned DEFAULT NULL COMMENT 'Numeric portion of address number on the street',
  `street_number_suffix` varchar(8) DEFAULT NULL COMMENT 'Non-numeric portion of address number on the street',
  `street_number_predirectional` varchar(8) DEFAULT NULL COMMENT 'Directional prefix',
  `street_name` varchar(64) DEFAULT NULL COMMENT 'Actual street name',
  `street_type` varchar(8) DEFAULT NULL COMMENT 'St, Rd, Ave, etc.',
  `street_number_postdirectional` varchar(8) DEFAULT NULL COMMENT 'Directional suffix',
  `street_unit` varchar(16) DEFAULT NULL COMMENT 'Secondary unit designator',
  `supplemental_address_1` varchar(96) DEFAULT NULL COMMENT 'Supplemental Address Information, Line 1',
  `supplemental_address_2` varchar(96) DEFAULT NULL COMMENT 'Supplemental Address Information, Line 2',
  `supplemental_address_3` varchar(96) DEFAULT NULL COMMENT 'Supplemental Address Information, Line 3',
  `city` varchar(64) DEFAULT NULL COMMENT 'City, Town or Village name',
  `county_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_county',
  `state_province_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_state_province',
  `postal_code_suffix` varchar(12) DEFAULT NULL COMMENT 'Store the suffix, like the +4 part in the USPS system',
  `postal_code` varchar(64) DEFAULT NULL COMMENT 'Store both US (zip5) and international postal codes',
  `usps_adc` varchar(32) DEFAULT NULL COMMENT 'USPS Bulk mailing code',
  `country_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_country',
  `geo_code_1` double DEFAULT NULL COMMENT 'Latitude',
  `geo_code_2` double DEFAULT NULL COMMENT 'Longitude',
  `manual_geo_code` tinyint(4) DEFAULT '0' COMMENT 'Is this a manually entered geo code',
  `timezone` varchar(8) DEFAULT NULL COMMENT 'Timezone expressed as a UTC offset',
  `name` varchar(255) DEFAULT NULL COMMENT 'Name of the address or location',
  `master_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_address',
  `start_date` datetime DEFAULT NULL COMMENT 'Start date for this address',
  `end_date` datetime DEFAULT NULL COMMENT 'End date for this address',
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'When was this address history record created',
  `modified_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When was this address history record last modified',
  `original_address_id` int(10) unsigned DEFAULT NULL COMMENT 'Original address ID from civicrm_address',
  PRIMARY KEY (`id`),
  KEY `FK_civicrm_address_history_contact_id` (`contact_id`),
  KEY `FK_civicrm_address_history_location_type_id` (`location_type_id`),
  KEY `FK_civicrm_address_history_county_id` (`county_id`),
  KEY `FK_civicrm_address_history_state_province_id` (`state_province_id`),
  KEY `FK_civicrm_address_history_country_id` (`country_id`),
  KEY `index_start_date` (`start_date`),
  KEY `index_end_date` (`end_date`),
  KEY `index_original_address_id` (`original_address_id`),
  CONSTRAINT `FK_civicrm_address_history_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_civicrm_address_history_location_type_id` FOREIGN KEY (`location_type_id`) REFERENCES `civicrm_location_type` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_civicrm_address_history_county_id` FOREIGN KEY (`county_id`) REFERENCES `civicrm_county` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_civicrm_address_history_state_province_id` FOREIGN KEY (`state_province_id`) REFERENCES `civicrm_state_province` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_civicrm_address_history_country_id` FOREIGN KEY (`country_id`) REFERENCES `civicrm_country` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Address History for Contacts';

-- Install the database triggers immediately after table creation
SOURCE triggers.sql;
