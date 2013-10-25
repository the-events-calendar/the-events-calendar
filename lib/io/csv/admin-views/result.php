<?php
/**
 * @var int[] $log Keys: 'created', 'updated', 'skipped'
 * @var int[] $skipped Row IDs of skipped/unparseable rows
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

require_once 'header.php';
?>

<h3><?php _e( 'Import Result', 'tribe-events-importer' ) ?></h3>

<p><strong><?php _e('Import complete!', 'tribe-events-importer'); ?></strong></p>
<ul>
	<li><?php printf(__('Inserted: %d', 'tribe-events-importer'), $log['created']); ?></li>
	<li><?php printf(__('Updated: %d', 'tribe-events-importer'), $log['updated']); ?></li>
	<li><?php printf(__('Skipped: %d', 'tribe-events-importer'), $log['skipped']); ?></li>
</ul>


<p><?php _e( 'The import statistics above have the following meaning:', 'tribe-events-importer') ?></p>
<?php _e( '<ol><li><strong>Inserted:</strong> A new item was inserted successfully.</li><li><strong>Updated:</strong> An item was found with the same name and/or start date. The existing item was updated with the new value from the file.</li><li><strong>Skipped:</strong> A row was found in the CSV file that could not be imported. Please see below for the invalid rows.</li></ol>', 'tribe-events-importer' ) ?>

<?php if ( !empty($skipped) ): ?>
	<p><?php printf(__('Skipped row numbers: %s', 'tribe-events-importer'), implode(', ', $skipped)); ?></p>
<?php endif; ?>

<?php
require_once 'footer.php';
?>
