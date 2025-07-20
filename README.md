# CiviCRM Address History Extension

This extension tracks the complete address history for contacts in CiviCRM using database triggers, providing comprehensive coverage of all address changes regardless of how they are made.

## Features

- **Database Trigger-Based Tracking**: Uses MySQL triggers to capture ALL address changes regardless of source (API, imports, direct SQL, third-party integrations, bulk operations)
- **Comprehensive Coverage**: Cannot be bypassed - tracks changes from any source that modifies the civicrm_address table
- **Automatic Address Tracking**: Automatically creates history records when addresses are added, modified, or deleted
- **Address History Tab**: Adds a new "Address History" tab to contact summary pages
- **Smart Change Detection**: Differentiates between significant changes (new history record) and minor updates (update existing record)
- **Start/End Dates**: Tracks when each address was active for a contact
- **Contact Merge Support**: Properly handles address history during contact merges
- **API Support**: Provides API endpoints for programmatic access to address history
- **Location Type Tracking**: Maintains location type information in history
- **Primary Address Handling**: Properly handles primary address changes and transitions

## Why Database Triggers?

This extension uses database triggers instead of CiviCRM hooks because:

- **Complete Coverage**: Captures changes from bulk imports, direct SQL operations, and third-party integrations
- **Reliability**: Cannot be bypassed by code that doesn't fire CiviCRM hooks
- **Performance**: Database-level operations are more efficient
- **Data Integrity**: Ensures no address changes are missed
- **Atomic Operations**: History tracking happens in the same transaction as address changes

## Installation

1. Download or clone this extension to your CiviCRM extensions directory
2. Navigate to **Administer → System Settings → Extensions**
3. Find "Address History" and click **Install**
4. The extension will automatically:
   - Create the `civicrm_address_history` table
   - Install database triggers on the `civicrm_address` table

## Database Schema

The extension creates a `civicrm_address_history` table with the following key fields:

- `contact_id` - FK to the contact
- `location_type_id` - FK to location type
- `start_date` - When this address became active
- `end_date` - When this address was deactivated (NULL for current addresses)
- `original_address_id` - FK to the original address record
- All standard address fields (street, city, state, country, etc.)

## Database Triggers

The extension installs three triggers on the `civicrm_address` table:

1. **civicrm_address_history_after_insert** - Creates history records for new addresses
2. **civicrm_address_history_after_update** - Handles address modifications
3. **civicrm_address_history_after_delete** - Ends history records for deleted addresses

## Usage

### Viewing Address History

1. Navigate to any contact record
2. Click the **Address History** tab
3. View the complete chronological history of addresses for that contact

The address history shows:
- Location type (Home, Work, etc.)
- Complete address information
- Start and end dates
- Current status (Active/Inactive)
- Primary address indicators

### Automatic Tracking

The extension automatically tracks address changes via database triggers:

- **New Address**: Creates a new history record with start date
- **Address Update**: 
  - For minor changes: Updates the existing history record
  - For significant changes: Ends the current record and creates a new one
- **Address Deletion**: Sets end date on the current history record
- **Primary Address Changes**: Properly handles when primary addresses change

### API Usage

The extension provides several API endpoints:

#### Get Address History
```php
$result = civicrm_api3('AddressHistory', 'get', [
  'contact_id' => 123,
  'location_type_id' => 1, // Optional filter
]);
```

#### Create Address History Record
```php
$result = civicrm_api3('AddressHistory', 'create', [
  'contact_id' => 123,
  'location_type_id' => 1,
  'street_address' => '123 Main St',
  'city' => 'Anytown',
  'state_province_id' => 1001,
  'postal_code' => '12345',
  'start_date' => '2023-01-01',
]);
```

#### Get Address History Count
```php
$result = civicrm_api3('AddressHistory', 'getcount', [
  'contact_id' => 123,
]);
```

## Contact Merging

When contacts are merged, the extension automatically:
- Transfers all address history from the duplicate contact to the main contact
- Maintains all historical data and relationships
- Preserves start and end dates

## Significant vs Minor Changes

The triggers differentiate between significant and minor address changes:

**Significant Changes** (create new history record):
- Street address changes
- City changes
- State/Province changes
- Country changes
- Location type changes
- Primary address designation changes

**Minor Changes** (update existing record):
- Supplemental address changes
- Geocoding updates
- Name/label changes

## File Structure

```
com.moellman.addresshistory/
├── addresshistory.php                          # Main extension file
├── info.xml                                    # Extension metadata
├── sql/
│   ├── auto_install.sql                        # Database schema + trigger installation
│   └── triggers.sql                            # Database triggers
├── CRM/
│   └── Addresshistory/
│       ├── BAO/
│       │   └── AddressHistory.php              # Business logic
│       ├── DAO/
│       │   └── AddressHistory.php              # Data access object
│       └── Page/
│           └── AddressHistory.php              # Page controller
├── templates/
│   └── CRM/
│       └── Addresshistory/
│           └── Page/
│               └── AddressHistory.tpl          # Display template
├── xml/
│   └── Menu/
│       └── addresshistory.xml                  # Menu configuration
├── api/
│   └── v3/
│       └── AddressHistory.php                  # API endpoints
└── README.md                                   # This file
```

## Permissions

The extension respects CiviCRM's standard contact permissions:
- Users need "view contact" permission to see address history
- Users need "edit contact" permission to modify address history via API

## Troubleshooting

### Extension Not Installing
- Check that your CiviCRM version is 5.0 or higher
- Ensure the extensions directory is writable
- Check the CiviCRM log for installation errors

### Address History Not Appearing
- Clear CiviCRM caches (Administer → System Settings → Cleanup Caches)
- Check that the extension is enabled
- Verify the contact has address records

### History Records Not Creating
- Check the CiviCRM error log for PHP errors
- Ensure the civicrm_address_history table was created
- Verify database triggers are installed:
  ```sql
  SHOW TRIGGERS LIKE 'civicrm_address';
  ```
- Verify database permissions for trigger creation

### Checking Trigger Status
You can verify triggers are working by checking:
```php
$status = CRM_Addresshistory_BAO_AddressHistory::checkTriggerStatus();
if ($status['installed']) {
  echo "All triggers are installed and active";
} else {
  echo "Missing triggers: " . implode(', ', $status['missing']);
}
```

## Development

### Triggers Used
- `civicrm_address_history_after_insert` - Creates history on address insert
- `civicrm_address_history_after_update` - Handles address updates
- `civicrm_address_history_after_delete` - Ends history on address delete

### Hooks Used
- `hook_civicrm_tabset()` - Adds address history tab
- `hook_civicrm_merge()` - Handles contact merges

### Key Classes
- `CRM_Addresshistory_BAO_AddressHistory` - Main business logic
- `CRM_Addresshistory_DAO_AddressHistory` - Database access
- `CRM_Addresshistory_Page_AddressHistory` - Display controller

## Requirements

- CiviCRM 5.0 or higher
- MySQL/MariaDB database
- Database user must have TRIGGER privileges

## Support

For issues, feature requests, or contributions:
1. Submit issues to: https://github.com/kenmoellman/civi_addresshistory/issues
2. Check the CiviCRM community forums
3. Review the extension documentation

## License

This extension is licensed under AGPL-3.0, the same license as CiviCRM.

## Changelog

### Version 0.5.0 (Beta)
- Initial release
- Database trigger-based address history tracking
- Address history tab on contact pages
- API support for programmatic access
- Contact merge support
- Comprehensive coverage of all address changes

The extension creates a `civicrm_address_history` table with the following key fields:

- `contact_id` - FK to the contact
- `location_type_id` - FK to location type
- `start_date` - When this address became active
- `end_date` - When this address was deactivated (NULL for current addresses)
- `original_address_id` - FK to the original address record
- All standard address fields (street, city, state, country, etc.)

## Usage

### Viewing Address History

1. Navigate to any contact record
2. Click the **Address History** tab
3. View the complete chronological history of addresses for that contact

The address history shows:
- Location type (Home, Work, etc.)
- Complete address information
- Start and end dates
- Current status (Active/Inactive)
- Primary address indicators

### Automatic Tracking

The extension automatically tracks address changes:

- **New Address**: Creates a new history record with start date
- **Address Update**: 
  - For minor changes: Updates the existing history record
  - For significant changes: Ends the current record and creates a new one
- **Address Deletion**: Sets end date on the current history record
- **Primary Address Changes**: Properly handles when primary addresses change

### API Usage

The extension provides several API endpoints:

#### Get Address History
```php
$result = civicrm_api3('AddressHistory', 'get', [
  'contact_id' => 123,
  'location_type_id' => 1, // Optional filter
]);
```

#### Create Address History Record
```php
$result = civicrm_api3('AddressHistory', 'create', [
  'contact_id' => 123,
  'location_type_id' => 1,
  'street_address' => '123 Main St',
  'city' => 'Anytown',
  'state_province_id' => 1001,
  'postal_code' => '12345',
  'start_date' => '2023-01-01',
]);
```

#### Get Address History Count
```php
$result = civicrm_api3('AddressHistory', 'getcount', [
  'contact_id' => 123,
]);
```

## Contact Merging

When contacts are merged, the extension automatically:
- Transfers all address history from the duplicate contact to the main contact
- Maintains all historical data and relationships
- Preserves start and end dates

## Significant vs Minor Changes

The extension differentiates between significant and minor address changes:

**Significant Changes** (create new history record):
- Street address changes
- City changes
- State/Province changes
- Country changes
- Location type changes
- Primary address designation changes

**Minor Changes** (update existing record):
- Supplemental address changes
- Geocoding updates
- Name/label changes

## File Structure

```
com.example.addresshistory/
├── addresshistory.php                          # Main extension file
├── info.xml                                    # Extension metadata
├── sql/
│   └── auto_install.sql                        # Database schema
├── CRM/
│   └── Addresshistory/
│       ├── BAO/
│       │   └── AddressHistory.php              # Business logic
│       ├── DAO/
│       │   └── AddressHistory.php              # Data access object
│       └── Page/
│           └── AddressHistory.php              # Page controller
├── templates/
│   └── CRM/
│       └── Addresshistory/
│           └── Page/
│               └── AddressHistory.tpl          # Display template
├── xml/
│   └── Menu/
│       └── addresshistory.xml                  # Menu configuration
├── api/
│   └── v3/
│       └── AddressHistory.php                  # API endpoints
└── README.md                                   # This file
```

## Permissions

The extension respects CiviCRM's standard contact permissions:
- Users need "view contact" permission to see address history
- Users need "edit contact" permission to modify address history via API
- Address history automatically updates based on address edit permissions

## Troubleshooting

### Extension Not Installing
- Check that your CiviCRM version is 5.0 or higher
- Ensure the extensions directory is writable
- Check the CiviCRM log for installation errors

### Address History Not Appearing
- Clear CiviCRM caches (Administer → System Settings → Cleanup Caches)
- Check that the extension is enabled
- Verify the contact has address records

### History Records Not Creating
- Check the CiviCRM error log for PHP errors
- Ensure the civicrm_address_history table was created
- Verify database permissions

## Development

### Hooks Used
- `hook_civicrm_post()` - Tracks address changes
- `hook_civicrm_tabset()` - Adds address history tab
- `hook_civicrm_merge()` - Handles contact merges

### Key Classes
- `CRM_Addresshistory_BAO_AddressHistory` - Main business logic
- `CRM_Addresshistory_DAO_AddressHistory` - Database access
- `CRM_Addresshistory_Page_AddressHistory` - Display controller

## Support

For issues, feature requests, or contributions:
1. Check the CiviCRM community forums
2. Review the extension documentation
3. Submit issues to the project repository

## License

This extension is licensed under AGPL-3.0, the same license as CiviCRM.

## Changelog

### Version 1.0.0
- Initial release
- Basic address history tracking
- Address history tab
- API support
- Contact merge support
