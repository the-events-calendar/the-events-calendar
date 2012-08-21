<?php
// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$importer_instance = ECP_Events_importer::instance();

require_once 'header.php';
?>
	<p class="error"><strong><?php _e( 'Please import venues and organizers <i>before</i> events.', 'tribe-events-importer' ) ?></strong></p>
	<p><?php echo _e( '<ol><li><strong>Organizer import requires:</strong> Organizer Name</li><li><strong>Venue import requires:</strong> Venue Name</li><li><strong>Event import requires:</strong> Event Name and Event Start Date</li></ol>', 'tribe-events-importer' ) ?></p>
	<p><?php _e( 'To begin importing data, please choose the type of import and the CSV file.', 'tribe-events-importer' ) ?></p>
	
	<form method="POST" enctype="multipart/form-data">
	    <table class="form-table">
		<tr>
		    <td>
			<label title="Import Type">
			    <select name="import_type" id="events-import-import-type">
			        <option value=""></option>
			        <option value="venues"><?php _e( 'Venues', 'tribe-events-importer' ) ?></option>
			        <option value="organizers"><?php _e( 'Organizers', 'tribe-events-importer' ) ?></option>
			        <option value="events"><?php _e( 'Events', 'tribe-events-importer' ) ?></option>
			    </select>
			    <?php _e( 'Import Type', 'tribe-events-importer' ) ?>
			</label>
		    </td>
		</tr>
		<tr>
		    <td>
			<label title="File">
			    <input type="file" name="import_file" id="events-import-csv-file"/>
			    <?php _e( 'File', 'tribe-events-importer' ) ?>
			</label>
		    </td>
		</tr>

		<tr>
		    <td>
			<label title="Header Row">
			    <input type="checkbox" name="import_header" value="1" id="events-import-csv-file"/>
			    <?php _e( 'This file has column names in the first row', 'tribe-events-importer' ) ?>
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
		  <td>
		    <input type="submit" class="button-primary" style="" value="<?php _e( 'Import CSV File', 'tribe-events-importer' ) ?>" />
		    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo (2 * 1024 * 1024); ?>" />
		    <input type="hidden" name="ecp_import_action" value="map" />
		  </td>
		</tr>
	    </table>
	</form>

<?php
require_once 'footer.php';
?>