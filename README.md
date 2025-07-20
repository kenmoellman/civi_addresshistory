# CiviCRM Address History Extension

This extension tracks the complete address history for contacts in CiviCRM using database triggers, providing comprehensive coverage of all address changes regardless of how they are made.

## Features

- **Database Trigger-Based Tracking**: Uses MySQL triggers to capture ALL address changes regardless of source (API, imports, direct SQL, third-party integrations, bulk operations)
- **Comprehensive Coverage**: Cannot be bypassed - tracks changes from any source that modifies the civicrm_address table
- **Automatic Population**: Populates history with existing addresses during installation
- **Administrator Editing**: Administrators can edit address history start/end dates
- **Address History Tab**: Adds a new "Address History" tab to contact summary pages
- **Smart Change Detection**: Differentiates between significant changes (new history record) and minor updates (update existing record)
- **Start/End Dates**: Tracks when each address was active for a contact
- **Contact Merge Support**: Properly handles address history during contact merges
- **API Support**: Provides API endpoints for programmatic access to address history
- **SearchKit Integration**: Address history data is searchable in SearchKit (CiviCRM 5.47+)
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
   - Populate the history table with all existing addresses

## Requirements

- CiviCRM 5.47 or higher (for SearchKit integration)
- MySQL/MariaDB database
- Database user must have TRIGGER privileges

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
- Edit links (for administrators)

### Editing Address History (Administrators Only)

1. Navigate to the Address History tab for a contact
2. Click the "Edit" link next to any address history record
3. Modify the start and/or end dates
4. Click "Save" to update the record

**Note**: Only users with "administer CiviCRM" permission can edit address history.

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

### SearchKit Integration

Address history data is fully integrated with SearchKit, allowing you to:

- Create searches across address history data
- Build custom displays and dashboards
- Create automated workflows based on address changes
- Generate reports on address history patterns

To access in SearchKit:
1. Go to **Search → Search Kit**
2. Select "Address History" as your entity
3. Build your search criteria and display

## Contact Merging

When contacts are merged, the extension automatically:
- Transfers all address history from the duplicate contact to the main contact
- Maintains all historical data and relationships
- Preserves start and end dates

## Significant vs Minor Changes

The triggers differentiate between significant and minor address changes:

**Significant Changes** (create new history
