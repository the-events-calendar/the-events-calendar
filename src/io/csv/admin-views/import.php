<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once 'header.php';
?>
<?php if ( ! empty( $messages ) ): ?>
	<div class="error">
		<?php foreach ( $messages as $message ): ?>
			<p><?php echo $message; ?></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
	<div id="modern-tribe-info">
		<h3><?php _e( 'Import Instructions', 'tribe-events-calendar' ); ?></h3>
		<ul>
			<li>
				<?php _e( 'If your events have Organizers or Venues, please import those first.', 'tribe-events-calendar' ); ?>
				<?php _e( 'To import organizers or venues:', 'tribe-events-calendar' ); ?>
				<ul>
					<li><?php _e( 'Select the appropriate import type.', 'tribe-events-calendar' ); ?></li>
					<li><?php _e( 'Upload a CSV file with one record on each line. The first line may contain column names (check the box below).', 'tribe-events-calendar' ); ?></li>
					<li><?php _e( 'One column in your CSV should have the Organizer/Venue name. All other fields are optional.', 'tribe-events-calendar' ); ?></li>
					<li><?php _e( "After you upload your file, you'll have the opportunity to indicate how the columns in your CSV map to fields in The Events Calendar.", 'tribe-events-calendar' ); ?></li>
				</ul>
			<li><?php _e( 'After importing your Organizers and Venues, import your Events:', 'tribe-events-calendar' ); ?>
				<ul>
					<li><?php _e( 'Upload a CSV file with one record on each line. The first line may contain column names (check the box below).', 'tribe-events-calendar' ); ?></li>
					<li><?php _e( 'One column in your CSV should have the Event title. Another should have the Event start date. All other fields are optional.', 'tribe-events-calendar' ); ?></li>
					<li><?php _e( "After you upload your file, you'll have the opportunity to indicate how the columns in your CSV map to fields in The Events Calendar.", 'tribe-events-calendar' ); ?></li>
				</ul>
			</li>
		</ul>
		<p><?php printf( __( 'Questions? <a href="%s">Watch the video</a>.', 'tribe-events-calendar' ), 'http://tri.be/using-the-events-calendars-csv-importer/' ); ?></p>
	</div>
	<form method="post" enctype="multipart/form-data">
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
						<input type="file" name="import_file" id="events-import-csv-file" />
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
						   value="<?php _e( 'Import CSV File', 'tribe-events-calendar' ) ?>" />
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo( 2 * 1024 * 1024 ); ?>" />
					<input type="hidden" name="ecp_import_action" value="map" />
				</td>
			</tr>
		</table>
	</form>

<?php
require_once 'footer.php';
?>