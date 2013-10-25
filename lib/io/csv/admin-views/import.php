<?php
// Don't load directly
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once 'header.php';
?>
<?php if ( !empty( $messages ) ): ?>
	<div class="error">
		<?php foreach ( $messages as $message): ?>
		<p><?php echo $message; ?></p>
	<?php endforeach; ?>
	</div>
<?php endif; ?>
	<p class="error">
		<strong><?php _e( 'Please import venues and organizers <i>before</i> events.', 'tribe-events-calendar' ) ?></strong>
	</p>
	<p><?php echo _e( '<ol><li><strong>Organizer import requires:</strong> Organizer Name</li><li><strong>Venue import requires:</strong> Venue Name</li><li><strong>Event import requires:</strong> Event Name and Event Start Date</li></ol>', 'tribe-events-calendar' ) ?></p>
	<p><?php _e( 'To begin importing data, please choose the type of import and the CSV file.', 'tribe-events-calendar' ) ?></p>

	<form method="POST" enctype="multipart/form-data">
		<table class="form-table">
			<tr>
				<td>
					<label title="Import Type">
						<?php _e( 'Import Type:', 'tribe-events-calendar' ) ?>
						<select name="import_type" id="events-import-import-type">
							<option value="venues"><?php _e( 'Venues', 'tribe-events-calendar' ) ?></option>
							<option value="organizers"><?php _e( 'Organizers', 'tribe-events-calendar' ) ?></option>
							<option value="events" selected="selected"><?php _e( 'Events', 'tribe-events-calendar' ) ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label title="File">
						<?php _e( 'CSV File:', 'tribe-events-calendar' ) ?>
						<input type="file" name="import_file" id="events-import-csv-file"/>
					</label>
					<p class="description"><?php _e( "Upload a properly formatted, UTF-8 encoded CSV file. Not sure if your file is UTF-8 encoded? Make sure to specify the character encoding when you save the file, or pass it through a <a href='http://i-tools.org/charset/exec?dest=utf-8&src=auto&download=1'>conversion tool</a>.", 'tribe-events-calendar' ); ?></p>
				</td>
			</tr>

			<tr>
				<td>
					<label title="Header Row">
						<input type="checkbox" name="import_header" value="1" id="events-import-csv-file" checked="checked" />
						<?php _e( 'This file has column names in the first row', 'tribe-events-calendar' ) ?>
					</label>
				</td>
			</tr>

		</table>

		<table class="form-table">
			<tr>
				<td>
					<input type="submit" class="button-primary" style=""
					       value="<?php _e( 'Import CSV File', 'tribe-events-calendar' ) ?>"/>
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo( 2 * 1024 * 1024 ); ?>"/>
					<input type="hidden" name="ecp_import_action" value="map"/>
				</td>
			</tr>
		</table>
	</form>

<?php
require_once 'footer.php';
?>