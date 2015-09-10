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

<h2><?php esc_html_e( 'Import Result', 'the-events-calendar' ) ?></h2>

<p><strong><?php esc_html_e( 'Import complete!', 'the-events-calendar' ); ?></strong></p>
<ul>
	<li><?php printf( __( 'Inserted: %d', 'the-events-calendar' ), $log['created'] ); ?></li>
	<li><?php printf( __( 'Updated: %d', 'the-events-calendar' ), $log['updated'] ); ?></li>
	<li><?php printf( __( 'Skipped: %d', 'the-events-calendar' ), $log['skipped'] ); ?></li>
</ul>


<p><?php esc_html_e( 'The import statistics above have the following meaning:', 'the-events-calendar' ) ?></p>
<?php _e( '<ol><li><strong>Inserted:</strong> A new item was inserted successfully.</li><li><strong>Updated:</strong> An item was found with the same name and/or start date. The existing item was updated with the new value from the file.</li><li><strong>Skipped:</strong> A row was found in the CSV file that could not be imported. Please see below for the invalid rows.</li></ol>', 'the-events-calendar' ) ?>

<?php if ( ! empty( $skipped ) ): ?>
	<p><?php printf( __( 'Skipped row numbers: %s', 'the-events-calendar' ), implode( ', ', $skipped ) ); ?></p>
<?php endif; ?>

<?php
require_once 'footer.php';
