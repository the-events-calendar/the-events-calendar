<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once 'header.php';

if ( ! empty( $messages ) ): ?>
	<div class="error">
		<?php foreach ( $messages as $message ): ?>
			<p><?php echo $message; ?></p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
	<div id="modern-tribe-info">
		<h2><?php esc_html_e( 'Import Instructions', 'the-events-calendar' ); ?></h2>
		<ul>
			<li>
				<?php esc_html_e( 'If your events have Organizers or Venues, please import those first.', 'the-events-calendar' ); ?>
				<?php esc_html_e( 'To import organizers or venues:', 'the-events-calendar' ); ?>
				<ul>
					<li><?php esc_html_e( 'Select the appropriate import type.', 'the-events-calendar' ); ?></li>
					<li><?php esc_html_e( 'Upload a CSV file with one record on each line. The first line may contain column names (check the box below).', 'the-events-calendar' ); ?></li>
					<li><?php esc_html_e( 'One column in your CSV should have the Organizer/Venue name. All other fields are optional.', 'the-events-calendar' ); ?></li>
					<li><?php esc_html_e( "After you upload your file, you'll have the opportunity to indicate how the columns in your CSV map to fields in The Events Calendar.", 'the-events-calendar' ); ?></li>
				</ul>
			<li><?php esc_html_e( 'After importing your Organizers and Venues, import your Events:', 'the-events-calendar' ); ?>
				<ul>
					<li><?php esc_html_e( 'Upload a CSV file with one record on each line. The first line may contain column names (check the box below).', 'the-events-calendar' ); ?></li>
					<li><?php esc_html_e( 'One column in your CSV should have the Event title. Another should have the Event start date. All other fields are optional.', 'the-events-calendar' ); ?></li>
					<li><?php esc_html_e( "After you upload your file, you'll have the opportunity to indicate how the columns in your CSV map to fields in The Events Calendar.", 'the-events-calendar' ); ?></li>
				</ul>
			</li>
		</ul>
		<p><?php printf( esc_html__( 'Questions? %sWatch the video%s.', 'the-events-calendar' ), '<a href="http://tri.be/using-the-events-calendars-csv-importer/">', '</a>' ); ?></p>
	</div>
	<form method="post" enctype="multipart/form-data" id="import">
		<table class="form-table">
			<tr>
				<td>
					<label title="Import Type">
						<?php esc_html_e( 'Import Type:', 'the-events-calendar' ) ?>
						<select name="import_type" id="events-import-import-type">
							<?php foreach ( $import_options as $value => $label ) : ?>
								<option value="<?php echo $value ?>" <?php selected( $value == $default_selected_import_option ); ?>><?php echo $label; ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label title="File">
						<?php esc_html_e( 'CSV File:', 'the-events-calendar' ) ?>
						<input type="file" name="import_file" id="events-import-csv-file" />
					</label>

					<p class="description"><?php printf( esc_html__( 'Upload a properly formatted, UTF-8 encoded CSV file. Not sure if your file is UTF-8 encoded? Make sure to specify the character encoding when you save the file, or pass it through a %sconversion tool%s.', 'the-events-calendar' ), '<a href="http://www.webutils.pl/index.php?idx=conv">', '</a>' ); ?></p>
				</td>
			</tr>

			<tr>
				<td>
					<label title="Header Row">
						<input type="checkbox" name="import_header" value="1" id="events-import-header" checked="checked" />
						<?php esc_html_e( 'This file has column names in the first row', 'the-events-calendar' ) ?>
					</label>
				</td>
			</tr>

		</table>

		<table class="form-table">
			<tr>
				<td>
					<input type="submit" class="button-primary" style=""
						   value="<?php esc_attr_e( 'Import CSV File', 'the-events-calendar' ) ?>" />
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php esc_attr_e( 2 * 1024 * 1024 ); ?>" />
					<input type="hidden" name="ecp_import_action" value="map" />
				</td>
			</tr>
		</table>
	</form>

<?php
require_once 'footer.php';
