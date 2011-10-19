<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();
?>

<h2><?php _e( 'Import Result', 'tribe-events-calendar-pro' ) ?></h2>

<?php if ( $error_message != '' ): ?>
    <p><?php _e( 'There was an error:', 'tribe-events-calendar-pro' ) ?> <?php echo $error_message ?></p>
    <p><a href="#" onClick="history.go(-1);return false;"><?php _e( 'Go back', 'tribe-events-calendar-pro' ) ?></a></p>
<?php else: ?>
    <p><?php _e( 'The import statistics below have the following meaning:', 'tribe-events-calendar-pro') ?></p>
    <p><?php _e( '<ol><li><strong>Inserted:</strong> A new item was inserted successfully.</li><li><strong>Updated:</strong> An item was found with the same name and/or start date. The existing item was updated with the new value from the file.</li><li><strong>Failed:</strong> A row was found in the CSV file that could not be imported. Please see below for the invalid rows.</li></ol>', 'tribe-events-calendar-pro' ) ?></p>
    <br />
    <p><?php echo $success_message ?></p>
<?php endif ?>
