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

<h3><?php esc_html_e( 'Import Result', 'tribe-events-calendar' ) ?></h3>

<p><strong><?php esc_html_e( 'Import complete!', 'tribe-events-calendar' ); ?></strong></p>
<ul>
	<li><?php printf( esc_html__( 'Inserted: %d', 'tribe-events-calendar' ), $log['created'] ); ?></li>
	<li><?php printf( esc_html__( 'Updated: %d', 'tribe-events-calendar' ), $log['updated'] ); ?></li>
	<li><?php printf( esc_html__( 'Skipped: %d', 'tribe-events-calendar' ), $log['skipped'] ); ?></li>
</ul>


<p><?php esc_html_e( 'The import statistics above have the following meaning:', 'tribe-events-calendar' ) ?></p>
<?php printf( esc_html__( '%1$s%2$s%3$sInserted:%4$s A new item was inserted successfully. %5$s%2$s%3$sUpdated:%4$s An item was found with the same name and/or start date. The existing item was updated with the new value from the file.%5$s%2$s%3$sSkipped:%4$s A row was found in the CSV file that could not be imported. Please see below for the invalid rows.%5$s%6$s', 'tribe-events-calendar' ), '<ol>', '<li>', '<strong>', '</strong>', '</li>', '</ol>' )?>

<?php if ( ! empty( $skipped ) ): ?>
	<p><?php printf( esc_html__( 'Skipped row numbers: %s', 'tribe-events-calendar' ), implode( ', ', $skipped ) ); ?></p>
<?php endif; ?>

<?php
require_once 'footer.php';
