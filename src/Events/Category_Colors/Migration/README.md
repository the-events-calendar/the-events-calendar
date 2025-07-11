# Category Colors Migration System

## Overview
The Category Colors Migration system handles migrating category color data from the old structure (`teccc_options`) to the new format using term meta and structured options. It's designed to be reliable, user-friendly, and maintain data integrity.

## Core Components

### 1. Processors
The Processors handle the actual data processing in a specific order:

#### Pre_Processor (First)
- Extracts and formats category color settings from old options
- Validates the structure of terms data
- Maps old settings to new format
- Prepares data for validation

#### Validator (Second)
- Validates the preprocessed data
- Checks for required fields
- Validates settings values
- Ensures category existence
- Verifies random samples of settings
- Validates meta keys

#### Worker (Third)
- Processes categories in batches of 100
- Handles the actual migration of data
- Updates term meta for each category
- Tracks progress and handles errors
- Manages batch processing

#### Post_Processor (Last)
- Verifies successful migration
- Validates migrated category meta data
- Logs any inconsistencies
- Ensures data integrity

> Each processor extends `Abstract_Migration_Step` and implements `Migration_Step_Interface`, providing a consistent interface and common functionality.

### 2. Scheduler
The Scheduler uses Action Scheduler to manage the migration process in a controlled, asynchronous way:

#### Abstract_Action
- Base class for all scheduler actions
- Handles common scheduling functionality
- Manages status updates
- Provides logging capabilities
- Controls action lifecycle

#### Action Flow
```
Preprocessing_Action
↓ (on success)
Validation_Action
↓ (on success)
Execution_Action (with batching)
↓ (on success)
Postprocessing_Action
```

#### Each Action
- Has specific status states (scheduled, in_progress, completed, failed)
- Can only be scheduled in valid states
- Handles its own error states
- Schedules the next action in sequence
- Uses Action Scheduler for reliable execution

#### Batching
- Execution_Action processes categories in batches
- Each batch is scheduled as a separate action
- Progress is tracked between batches
- Final batch triggers postprocessing

### 3. Notice System
The Notice system provides user interaction and feedback:

#### Migration_Notice
- Handles admin UI display
- Shows different messages based on migration status
- Provides action buttons (Start/Restart Migration)
- Includes progress updates
- Shows error messages when needed

#### Migration_Flow
- Controls when notices should appear
- Manages migration initialization
- Handles user actions
- Coordinates with scheduler
- Provides progress information

#### Notice States
- Initial prompt (when migration is needed)
- In-progress updates (shows current step)
- Success messages
- Error states with recovery options

#### User Interaction
- Start Migration button
- Restart Migration button (on failure)
- Learn More link
- Progress indicators
- Error messages with details

## Testing Instructions

### Manual Testing
1. The migration notice will appear automatically if:
   - The old Category Colors plugin is active, OR
   - A previous migration attempt was incomplete
2. Click "Start Migration" to begin the process
3. Monitor progress in the admin notice
4. Check category colors after completion

### Debugging
- Use the WordPress debug log to track migration progress
- Check the migration status in the options table
- Review category meta data after migration

### Error Recovery
If the migration fails:
1. The notice will show an error message
2. Click "Restart Migration" to try again
3. Check the debug log for specific error details

## Available Filters

### Notice System
```php
/**
 * Force the migration notice to appear.
 *
 * @since 6.14.0
 *
 * @param bool $force_show Whether to force show the notice.
 * @return bool Modified value.
 */
add_filter( 'tec_events_category_colors_force_migration_notice', '__return_true' );
```

### Batch Processing
```php
/**
 * Modify the number of categories processed in each batch.
 *
 * @since 6.14.0
 *
 * @param int $batch_size The number of categories to process per batch.
 * @return int Modified batch size.
 */
add_filter( 'tec_events_category_colors_migration_batch_size', function() {
    return 200; // Process 200 categories per batch instead of default 100
} );
```

### Validation
```php
/**
 * Modify the validation sample size for settings.
 *
 * @since 6.14.0
 *
 * @param int $sample_size The number of settings to validate.
 * @return int Modified sample size.
 */
add_filter( 'tec_events_category_colors_migration_validation_sample_size', function() {
    return 50; // Validate 50 settings instead of default 25
} );
```

### Action Scheduling
```php
/**
 * Prevent or allow scheduling of a migration action.
 *
 * @since 6.14.0
 *
 * @param bool|WP_Error $can_schedule Whether the action can be scheduled.
 * @param Abstract_Action $action The action being scheduled.
 * @return bool|WP_Error Modified value.
 */
add_filter( 'tec_events_category_colors_migration_pre_schedule_action', function( $can_schedule, $action ) {
    // Example: Prevent scheduling if certain conditions are met
    if ( $action instanceof Preprocessing_Action ) {
        return true; // Allow preprocessing
    }
    return $can_schedule;
}, 10, 2 );
```

### Status Updates
```php
/**
 * Modify migration status updates.
 *
 * @since 6.14.0
 *
 * @param string $status The new migration status.
 */
add_action( 'tec_events_category_colors_migration_status_updated', function( $status ) {
    // Example: Log status changes to a custom location
    error_log( "Migration status changed to: {$status}" );
} );
```

### Logging
```php
/**
 * Modify log messages before they are recorded.
 *
 * @since 6.14.0
 *
 * @param string $message The log message.
 * @param array $context Additional context data.
 * @return string Modified message.
 */
add_filter( 'tec_events_category_colors_migration_log_message', function( $message, $context ) {
    // Example: Add custom prefix to log messages
    return "[Custom Prefix] {$message}";
}, 10, 2 );
```