<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();
?>

<h2><?php _e( 'Import Result', 'tribe-events-calendar-pro' ) ?></h2>

<?php if ( $error_message != '' ): ?>
    <p><?php _e( 'There was an error:', 'tribe-events-calendar-pro' ) ?> <?php echo $error_message ?></p>
    <p><a href="#" onClick="history.go(-1)"><?php _e( 'Go back', 'tribe-events-calendar-pro' ) ?></a></p>
<?php else: ?>
    <p><?php echo $success_message ?></p>
<?php endif ?>