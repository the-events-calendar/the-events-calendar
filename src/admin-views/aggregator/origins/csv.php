<?php
$origin_slug        = 'csv';
$field              = (object) [];
$field->label       = __( 'Content Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Content Type', 'the-events-calendar' );
$field->help        = __( 'Specify the type of content you wish to import, e.g. events.', 'the-events-calendar' );
$field->help        .= '<br/>';
$field->help        .= __( 'For the best results, import venue and organizer files before importing event files.', 'the-events-calendar' );

$field->source      = 'csv_content_type';

$csv_record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( 'csv' );
$post_types = $csv_record->get_import_post_types();
$main       = Tribe__Events__Main::instance();
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="csv">
	<th scope="row">
		<label for="tribe-ea-field-csv_content_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<select
			name="aggregator[csv][content_type]"
			id="tribe-ea-field-csv_content_type"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			data-hide-search
			data-prevent-clear
		>
			<option value=""><?php echo esc_attr( $field->placeholder ); ?></option>
			<?php foreach ( $post_types as $post_type ) : ?>
				<option value="<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->labels->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>

<?php
$field              = (object) [];
$field->label       = __( 'Choose File:', 'the-events-calendar' );
$field->placeholder = __( 'Choose a CSV file', 'the-events-calendar' );
$field->help        = __( 'Select your .CSV file from the WordPress media library. You may need to first upload the file from your computer to the library.', 'the-events-calendar' );
$field->button      = __( 'Upload', 'the-events-calendar' );
$field->media_title = __( 'Upload a CSV File', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-csv_content_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[csv][file]"
			type="hidden"
			id="tribe-ea-field-csv_file"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<button
			class="tribe-ea-field tribe-ea-media_button tribe-dependent button button-secondary"
			data-input="tribe-ea-field-csv_file"
			data-media-title="<?php echo esc_attr( $field->media_title ); ?>"
			data-mime-type="text/csv"
			data-depends="#tribe-ea-field-csv_file"
			data-condition-empty
		>
			<?php echo esc_html( $field->button ); ?>
		</button>
		<span class="tribe-ea-fileicon dashicons dashicons-media-document"></span>
		<span class="tribe-ea-field tribe-ea-file-name" id="tribe-ea-field-csv_file_name"><?php echo esc_html__( 'No file chosen', 'the-events-calendar' ); ?></span>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>

<tr class="tribe-dependent" data-depends="#tribe-ea-field-csv_file" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>

<div class="tec-admin-ea-help-message" data-depends="#tribe-ea-field-origin" data-condition-not-empty data-condition-relation="and" data-condition-not='["url","eventbrite", "gcal", "ics", "ical", "meetup", "url"]'>
	<?php esc_html_e( 'Need assistance? You can find more information here:', 'the-events-calendar' ); ?>

	<img
		class="tec-admin-ea-help-message__icon"
		src="<?php echo esc_url( tribe_resource_url( 'images/icons/tec-horns.svg', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'The Events Calendar logo', 'the-events-calendar' ); ?>"
	/>
	<a href="https://evnt.is/1bam" target="_blank" rel="noopener noreferrer">
		<?php esc_html_e( 'Importing Calendar Data From a CSV File', 'the-events-calendar' ); ?>
	</a>

	<img
		class="tec-admin-ea-help-message__icon"
		src="<?php echo esc_url( tribe_resource_url( 'images/icons/tec-horns.svg', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'The Events Calendar logo', 'the-events-calendar' ); ?>"
	/>
	<a href="https://evnt.is/1ban" target="_blank" rel="noopener noreferrer">
		<?php esc_html_e( 'CSV File Formatting and Examples ', 'the-events-calendar' ); ?>
	</a>
</div>