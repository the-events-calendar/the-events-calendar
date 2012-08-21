<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();

require_once 'header.php';
?>

<h3><?php _e( 'Import Result', 'tribe-events-importer' ) ?></h3>

<?php if ( $error_message != '' ): ?>
    <p><?php _e( 'There was an error:', 'tribe-events-importer' ) ?> <?php echo $error_message ?></p>
    <p><a href="#" onClick="history.go(-1);return false;"><?php _e( 'Go back', 'tribe-events-importer' ) ?></a></p>
<?php else: ?>
    <p><?php _e( 'The import statistics below have the following meaning:', 'tribe-events-importer') ?></p>
    <p><?php _e( '<ol><li><strong>Inserted:</strong> A new item was inserted successfully.</li><li><strong>Updated:</strong> An item was found with the same name and/or start date. The existing item was updated with the new value from the file.</li><li><strong>Failed:</strong> A row was found in the CSV file that could not be imported. Please see below for the invalid rows.</li></ol>', 'tribe-events-importer' ) ?></p>
    <br />
    <p><?php echo $success_message ?></p>
<?php endif;
require_once 'footer.php';
?>
