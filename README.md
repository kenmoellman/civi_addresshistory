# CiviCRM Address History Extension

This extension provides comprehensive address history tracking for contacts in CiviCRM using database triggers, ensuring complete coverage of all address changes regardless of how they are made.

## Features

- **Database Trigger-Based Tracking**: Uses MySQL triggers to capture ALL address changes regardless of source (API, imports, direct SQL, third-party integrations, bulk operations)
- **Comprehensive Coverage**: Cannot be bypassed - tracks changes from any source that modifies the civicrm_address table
- **Automatic Population**: Populates history with existing addresses during installation
- **Administrator Editing**: Administrators can edit address history start/end dates and delete records
- **Address History Tab**: Adds a new "Address History" tab to contact summary pages
- **Smart Change Detection**: Differentiates between significant changes (new history record) and minor updates (update existing record)
- **Start/End Dates**: Tracks when each address was active for a contact
- **Contact Merge Support**: Properly handles address history during contact merges with user-selectable options
- **API Support**: Provides both APIv3 and APIv4 endpoints for programmatic access to address history
- **SearchKit Integration**: Address history data is searchable in SearchKit (CiviCRM 5.47+)
- **Location Type Tracking**: Maintains location type information in history
- **Primary Address Handling**: Properly handles primary address changes and transitions

## Why Database Triggers?

This extension uses database triggers instead of CiviCRM hooks because:

- **Complete Coverage**: Captures changes from bulk imports, direct SQL operations, and third-party integrations
- **Reliability**: Cannot be bypassed by code that doesn't fire CiviCRM hooks
- **Performance**: Database-level operations are more efficient than PHP hooks
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

- CiviCRM 6.0 or higher
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
2. **civicrm_address_history_after_update** - Handles address modifications intelligently
3. **civicrm_address_history_after_delete** - Ends history records for deleted addresses

## Usage

### Viewing Address History

1. Navigate to any contact record
2. Click the **Address History** tab
3. View the complete chronological history of addresses for that contact

The address history displays:
- Location type (Home, Work, etc.)
- Complete address information
- Start and end dates
- Current status (Active/Inactive)
- Primary address indicators
- Edit and Delete links (for administrators)

### Editing Address History (Administrators Only)

1. Navigate to the Address History tab for a contact
2. Click the "Edit" link next to any address history record
3. Modify the start and/or end dates:
   - **Start Date**: When the address became active
   - **End Date Options**:
     - Current (No End Date) - Address is still active
     - Specific End Date - Address ended on a specific date
4. Click "Save" to update the record

**Note**: Only users with "administer CiviCRM" permission can edit or delete address history.

### Deleting Address History (Administrators Only)

1. Navigate to the Address History tab for a contact
2. Click the "Delete" link next to any address history record
3. Confirm the deletion in the popup dialog
4. The history record will be permanently removed

**Warning**: Deleted address history records cannot be recovered.

### Automatic Tracking

The extension automatically tracks address changes via database triggers:

- **New Address**: Creates a new history record with start date
- **Address Update**: 
  - For significant changes (street, city, state, country, location type, primary status): Ends the current record and creates a new one
  - For minor changes (supplemental addresses, geocoding): Updates the existing history record
- **Address Deletion**: Sets end date on the current history record
- **Primary Address Changes**: Properly handles when primary addresses change

### Contact Merging

When merging contacts, the extension provides three options for handling address history:

1. **Move all address history to main contact** (Recommended)
   - Combines all address history into one complete timeline
   - Preserves chronological order and relationships

2. **Copy address history to main contact**
   - Creates duplicates of address history records
   - Use only if you need to preserve original records for audit purposes

3. **Keep address histories separate**
   - Address history from the duplicate contact will be deleted with the contact
   - Use when histories should not be combined

The merge interface displays the number of address history records for each contact and allows administrators to choose the appropriate action.

### API Usage

The extension provides both APIv3 (legacy) and APIv4 (recommended) endpoints for programmatic access.

#### APIv4 Usage (Recommended)

APIv4 provides a modern, more powerful interface with better SearchKit integration:

##### Get Address History
```php
// Basic get
$addressHistory = \Civi\Api4\AddressHistory::get()
  ->addWhere('contact_id', '=', 123)
  ->addOrderBy('start_date', 'DESC')
  ->execute();

// With filters and joins
$addressHistory = \Civi\Api4\AddressHistory::get()
  ->addSelect('*', 'contact_id.display_name', 'location_type_id.display_name')
  ->addWhere('contact_id', '=', 123)
  ->addWhere('location_type_id', '=', 1)
  ->addWhere('end_date', 'IS NULL') // Only current addresses
  ->addOrderBy('start_date', 'DESC')
  ->setLimit(25)
  ->execute();
```

###### Create Address History Record
```php
$result = \Civi\Api4\AddressHistory::create()
  ->setValues([
    'contact_id' => 123,
    'location_type_id' => 1,
    'street_address' => '123 Main St',
    'city' => 'Anytown',
    'state_province_id' => 1001,
    'postal_code' => '12345',
    'country_id' => 1228,
    'start_date' => '2023-01-01',
    'is_primary' => TRUE,
  ])
  ->execute();
```

##### Update Address History Record
```php
$result = \Civi\Api4\AddressHistory::update()
  ->addWhere('id', '=', 456)
  ->setValues([
    'end_date' => '2023-12-31',
  ])
  ->execute();
```

###### Get Address History Count
```php
$count = \Civi\Api4\AddressHistory::get()
  ->addWhere('contact_id', '=', 123)
  ->selectRowCount()
  ->execute()
  ->count();
```

###### Delete Address History Record
```php
$result = \Civi\Api4\AddressHistory::delete()
  ->addWhere('id', '=', 456)
  ->execute();
```

##### Advanced Queries
```php
// Get address history with location changes
$locationChanges = \Civi\Api4\AddressHistory::get()
  ->addSelect('*', 'contact_id.display_name')
  ->addWhere('contact_id', '=', 123)
  ->addWhere('end_date', 'IS NOT NULL')
  ->addGroupBy('location_type_id')
  ->addOrderBy('start_date', 'ASC')
  ->execute();

// Get contacts who moved in the last year
$recentMoves = \Civi\Api4\AddressHistory::get()
  ->addSelect('contact_id', 'COUNT(*) AS move_count')
  ->addWhere('start_date', '>=', date('Y-m-d', strtotime('-1 year')))
  ->addGroupBy('contact_id')
  ->addHaving('COUNT(*)', '>', 1)
  ->execute();
```

#### APIv3 Usage (Legacy)

APIv3 is still supported for backward compatibility:

##### Get Address History
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

#### Delete Address History Record
```php
$result = civicrm_api3('AddressHistory', 'delete', [
  'id' => 456,
]);
```

### SearchKit Integration

Address history data is fully integrated with SearchKit via APIv4, allowing you to:

- Create searches across address history data
- Build custom displays and dashboards
- Create automated workflows based on address changes
- Generate reports on address history patterns
- Export address history data

To access in SearchKit:
1. Go to **Search → Search Kit**
2. Select "Address History" as your entity
3. Build your search criteria and display

## Significant vs Minor Changes

The triggers differentiate between significant and minor address changes to maintain a clean history:

**Significant Changes** (create new history record):
- Street address changes
- City changes
- State/Province changes
- Country changes
- Location type changes
- Primary address designation changes

**Minor Changes** (update existing record):
- Supplemental address changes (lines 1, 2, 3)
- Geocoding updates (latitude/longitude)
- Name/label changes
- Billing designation changes
- County changes

## File Structure

```
com.moellman.addresshistory/
├── info.xml                                    # Extension metadata
├── addresshistory.php                          # Main extension file with hooks
├── addresshistory.civix.php                    # Civix framework code
├── README.md                                   # This documentation
├── sql/
│   ├── auto_install.sql                        # Database schema (auto-install)
│   ├── install.sql                             # Database schema (manual install)
│   ├── triggers.sql                            # Database triggers (reference)
│   └── uninstall.sql                           # Cleanup SQL
├── Civi/
│   └── Api4/
│       └── AddressHistory.php                  # APIv4 entity definition
│   └── Addresshistory/
│       ├── BAO/
│       │   └── AddressHistory.php              # Business logic layer
│       ├── DAO/
│       │   └── AddressHistory.php              # Data access object
│       ├── Page/
│       │   └── AddressHistory.php              # Page controller
│       ├── Form/
│       │   ├── EditHistory.php                 # Edit form controller
│       │   └── DeleteHistory.php               # Delete form controller
│       ├── Upgrader.php                        # Installation/upgrade logic
│       └── Upgrader/
│           └── Base.php                        # Base upgrader class
├── CRM/
│   └── CRM/
│       └── Addresshistory/
│           ├── Page/
│           │   └── AddressHistory.tpl          # Display template
│           └── Form/
│               ├── EditHistory.tpl             # Edit form template
│               └── DeleteHistory.tpl           # Delete form template
├── templates/
│   └── Menu/
│       └── addresshistory.xml                  # Menu configuration
└── api/
    └── v3/
        └── AddressHistory.php                  # API v3 endpoints
```

## Permissions

The extension respects CiviCRM's standard contact permissions:
- Users need "view contact" permission to see address history
- Users need "administer CiviCRM" permission to edit or delete address history
- API access requires appropriate contact view/edit permissions

## Troubleshooting

### Extension Not Installing
- Check that your CiviCRM version is 6.0 or higher
- Ensure the extensions directory is writable
- Check the CiviCRM log for installation errors
- Verify database user has TRIGGER privileges

### Address History Not Appearing
- Clear CiviCRM caches (**Administer → System Settings → Cleanup Caches**)
- Check that the extension is enabled
- Verify the contact has address records
- Check if the civicrm_address_history table was created

### History Records Not Creating
- Check the CiviCRM error log for PHP errors
- Ensure the civicrm_address_history table was created
- Verify database triggers are installed:
  ```sql
  SHOW TRIGGERS LIKE 'civicrm_address';
  ```
- Verify database permissions for trigger creation

### Checking Trigger Status
You can verify triggers are working:
```php
$status = CRM_Addresshistory_BAO_AddressHistory::checkTriggerStatus();
if ($status['installed']) {
  echo "All triggers are installed and active";
} else {
  echo "Missing triggers: " . implode(', ', $status['missing']);
}
```

### Population Issues
If existing addresses weren't populated during installation:
```php
// Re-run the population manually
CRM_Addresshistory_BAO_AddressHistory::populateExistingAddresses();
```

### Merge Issues
If address history isn't merging properly:
- Check that both contacts have address history records
- Verify the merge form shows the address history section
- Check CiviCRM logs for merge-related errors
- Ensure the selected merge action is being captured

## Performance Considerations

- Database triggers add minimal overhead to address operations
- Indexes are provided on key fields for efficient querying
- For very large databases (millions of addresses), consider:
  - Archiving old address history records
  - Creating additional indexes based on usage patterns
  - Monitoring trigger performance

## Development

### Hooks Used
- `hook_civicrm_triggerInfo()` - Defines database triggers
- `hook_civicrm_tabset()` - Adds address history tab
- `hook_civicrm_buildForm()` - Modifies merge form
- `hook_civicrm_postProcess()` - Handles merge form submission
- `hook_civicrm_merge()` - Handles batch merges
- `hook_civicrm_pre()` - Handles contact deletion during merge

### Key Classes
- `CRM_Addresshistory_BAO_AddressHistory` - Main business logic
- `CRM_Addresshistory_DAO_AddressHistory` - Database access
- `CRM_Addresshistory_Page_AddressHistory` - Display controller
- `CRM_Addresshistory_Form_EditHistory` - Edit form
- `CRM_Addresshistory_Form_DeleteHistory` - Delete form

### Database Triggers
- **INSERT**: Creates new history records for new addresses
- **UPDATE**: Handles both significant changes (new record) and minor updates
- **DELETE**: Ends history records when addresses are deleted

## Testing

To test the extension thoroughly:

1. **Basic Functionality**:
   - Add a new address to a contact → Verify it appears in address history
   - Edit an address significantly → Verify new history record with end date on old
   - Edit an address minimally → Verify existing record is updated
   - Delete an address → Verify history record gets end date

2. **Primary Address Handling**:
   - Set an address as primary → Verify it's marked in history
   - Add another address of same type as primary → Verify first address history ends

3. **Contact Merge**:
   - Create two contacts with address history
   - Merge with different options (move/copy/keep)
   - Verify address history is handled correctly

4. **Permissions**:
   - Test viewing as regular user → Should see history but no edit links
   - Test as administrator → Should see edit and delete options

5. **API Testing**:
   ```php
   // Test APIv4 endpoints (recommended)
   $result = \Civi\Api4\AddressHistory::get()
     ->addWhere('contact_id', '=', 123)
     ->execute();
   
   $count = \Civi\Api4\AddressHistory::get()
     ->addWhere('contact_id', '=', 123)
     ->selectRowCount()
     ->execute()
     ->count();
   
   // Test APIv3 endpoints (legacy)
   $result = civicrm_api3('AddressHistory', 'get', ['contact_id' => 123]);
   $result = civicrm_api3('AddressHistory', 'getcount', ['contact_id' => 123]);
   ```

## Version History

### Version 0.9.0 (Current)
- Initial alpha release
- Database trigger-based address history tracking
- Address history tab on contact pages
- Administrator editing and deletion capabilities
- API support for programmatic access
- Enhanced contact merge support with user options
- APIv4 support for modern CiviCRM integration (with APIv3 legacy support)
- SearchKit integration
- Comprehensive coverage of all address changes
- Automatic population of existing addresses during installation

## Support

For issues, feature requests, or contributions:
1. Submit issues to: https://github.com/kenmoellman/civi_addresshistory/issues
2. Check the CiviCRM community forums
3. Review the extension documentation

## License

This extension is licensed under AGPL-3.0, the same license as CiviCRM.

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes with appropriate tests
4. Submit a pull request

## Acknowledgments

- Built using the CiviCRM extension framework
- Uses database triggers for comprehensive tracking
- Integrates with CiviCRM's contact management system
- Compatible with SearchKit for advanced reporting
