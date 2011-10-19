<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();
?>

<style type="text/css">
div.tribe_settings{
        width:90%;
}
</style>
<div class="tribe_settings wrap">

    <h2><?php _e( 'Events Import', 'tribe-events-calendar-pro' ) ?></h2>

    <div class="form">
	<p class="error"><strong><?php _e('Please import venues and organizations first, followed by events.', 'tribe-events-calendar-pro' ) ?></strong></p>
	<p><?php _e( 'To begin importing events, please choose the type of import and the file.', 'tribe-events-calendar-pro' ) ?></p>
	
	<form method="POST" enctype="multipart/form-data">
	    <table class="form-table">
		<tr>
		    <td>
			<label title="Import Type">
			    <select name="import_type" id="events-import-import-type">
			        <option value=""></option>
			        <option value="venues"><?php _e( 'Venues', 'tribe-events-calendar-pro' ) ?></option>
			        <option value="organizers"><?php _e( 'Organizers', 'tribe-events-calendar-pro' ) ?></option>
			        <option value="events"><?php _e( 'Events', 'tribe-events-calendar-pro' ) ?></option>
			    </select>
			    <?php _e( 'Import Type', 'tribe-events-calendar-pro' ) ?>
			</label>
		    </td>
		</tr>
		<tr>
		    <td>
			<label title="File">
			    <input type="file" name="import_file" id="events-import-csv-file"/>
			    <?php _e( 'File', 'tribe-events-calendar-pro' ) ?>
			</label>
		    </td>
		</tr>
	    </table>
	    <?php
		// Check that we can upload to the server
		if ( !is_writable( $importer_instance->pluginPath ) ) {
		    echo "<br />" . sprintf( __( 'Please ensure the following directory is <a href="%s">writable by WordPress</a>: %s' ),
						'http://codex.wordpress.org/Changing_File_Permissions#Permission_Scheme_for_WordPress',
						'<code>' . $importer_instance->pluginPath . '</code>' ) . "<br />";
		} else {
		}
	    ?>
	    <table class="form-table">
		<tr>
		    <input type="submit" class="button-primary" style="" value="<?php _e( 'Import CSV File', 'tribe-events-calendar-pro' ) ?>" />
		    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (2 * 1024 * 1024); ?>" />
		    <input type="hidden" name="ecp_import_action" value="map" />
		</tr>
	    </table>
	</form>
    </div>
</div>
