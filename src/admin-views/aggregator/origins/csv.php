<?php
$field              = (object) array();
$field->label       = __( 'Content Type', 'the-events-calendar' );
$field->placeholder = __( 'Select Content Type', 'the-events-calendar' );
$field->help        = __( 'For better results, import venue and organizer files before importing event files', 'the-events-calendar' );
$field->source      = 'csv_content_type';
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="csv">
	<th scope="row">
		<label for="tribe-ea-field-content_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[csv][content_type]"
			type="hidden"
			id="tribe-ea-field-csv_content_type"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			data-hide-search=1
			data-source="<?php echo esc_attr( $field->source ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
	</td>
</tr>

<?php
$field              = (object) array();
$field->label       = __( 'Choose File', 'the-events-calendar' );
$field->placeholder = __( 'Choose a CSV file', 'the-events-calendar' );
$field->help        = __( 'Select your .CSV file from the WordPress media library. You may need to first upload the file from your computer to the library.', 'the-events-calendar' );
$field->source      = 'csv_files';
$field->button      = __( 'Upload new File', 'the-events-calendar' );
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
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
	</td>
</tr>

<tr class="tribe-dependent" data-depends="tribe-ea-field-csv_file" data-condition-not-empty>
	<td colspan="2">
		<div class="tribe-ea-table-container">
			<span class='spinner tribe-ea-active'></span>
		</div>
	</td>
</tr>
