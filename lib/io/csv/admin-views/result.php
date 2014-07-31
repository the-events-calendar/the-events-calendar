<?php
/**
 * @var int[] $log     Keys: 'created', 'updated', 'skipped'
 * @var int[] $skipped Row IDs of skipped/unparseable rows
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once 'header.php';
?>

<h3><?php _e( 'Import Result', 'tribe-events-calendar' ) ?></h3>

<p><strong><?php _e( 'Import complete!', 'tribe-events-calendar' ); ?></strong></p>
<ul>
	<li><?php printf( __( 'Inserted: %d', 'tribe-events-calendar' ), $log['created'] ); ?></li>
	<li><?php printf( __( 'Updated: %d', 'tribe-events-calendar' ), $log['updated'] ); ?></li>
	<li><?php printf( __( 'Skipped: %d', 'tribe-events-calendar' ), $log['skipped'] ); ?></li>
</ul>


<p><?php _e( 'The import statistics above have the following meaning:', 'tribe-events-calendar' ) ?></p>
<?php _e( '<ol><li><strong>Inserted:</strong> A new item was inserted successfully.</li><li><strong>Updated:</strong> An item was found with the same name and/or start date. The existing item was updated with the new value from the file.</li><li><strong>Skipped:</strong> A row was found in the CSV file that could not be imported. Please see below for the invalid rows.</li></ol>', 'tribe-events-calendar' ) ?>

<?php if ( ! empty( $skipped ) ): ?>
	<p><?php printf( __( 'Skipped row numbers: %s', 'tribe-events-calendar' ), implode( ', ', $skipped ) ); ?></p>
<?php endif; ?>

<?php
require_once 'footer.php';
?>
