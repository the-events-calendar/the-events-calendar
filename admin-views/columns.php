<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();
?>

<h2><?php _e( 'Column Mapping', 'tribe-events-calendar-pro' ) ?></h2>

<?php if ( $error_message != '' ): ?>
    <p><?php _e( 'There was an error:', 'tribe-events-calendar-pro' ) ?> <?php echo $error_message ?></p>
    <p><a href="#" onClick="history.go(-1)"><?php _e( 'Go back', 'tribe-events-calendar-pro' ) ?></a></p>
<?php else: ?>
    <div class="form">
        <p><?php _e( 'In order to import your file, you need to match up columns in the file, with attributes of events, venues and organizers.',
		    'tribe-events-calendar-pro' ) ?>
        </p>
	<form method="POST">
	    <table class="form-table">
	        <?php foreach ( $csv->titles as $col => $title): ?>
		    <tr>
			<td><?php echo $title ?></td>
			<td><?php echo $importer_instance->generateColumnSelects( $col, $title, $import_type ) ?></td>
		    </tr>
		<?php endforeach ?>
	    </table>
	    <table class="form-table">
		<tr>
		    <input type="submit" class="button-primary" style="" value="<?php _e( 'Perform Import', 'tribe-events-calendar-pro' ) ?>" />
		    <input type="hidden" name="import_type" value="<?php echo $import_type ?>" />
		    <input type="hidden" name="ecp_import_action" value="import" />
		</tr>
	    </table>	    
	</form>
    </div>
    
<?php endif ?>