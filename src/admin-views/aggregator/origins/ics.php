<?php
$tab                = $this->tabs->get_active();
$origin_slug        = 'ics';
$field              = (object) array();
$field->label       = __( 'Choose File:', 'the-events-calendar' );
$field->placeholder = __( 'Choose File', 'the-events-calendar' );
$field->help        = __( 'Select your ICS file from the WordPress media library. You may need to first upload the file from your computer to the library.', 'the-events-calendar' );
$field->source      = 'ics_files';
$field->button      = __( 'Upload', 'the-events-calendar' );
$field->media_title = __( 'Upload an ICS File', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="ics">
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[ics][file]"
			type="hidden"
			id="tribe-ea-field-ics_file"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<button
			class="tribe-ea-field tribe-ea-media_button tribe-dependent button button-secondary"
			data-input="tribe-ea-field-ics_file"
			data-media-title="<?php echo esc_attr( $field->media_title ); ?>"
			data-mime-type="text/calendar"
			data-depends="#tribe-ea-field-ics_file"
			data-condition-empty
		>
			<?php echo esc_html( $field->button ); ?>
		</button>
		<span class="tribe-ea-fileicon dashicons dashicons-media-document"></span>
		<span class="tribe-ea-field tribe-ea-file-name" id="tribe-ea-field-ics_file_name"><?php echo esc_html__( 'No file chosen', 'the-events-calendar' ); ?></span>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>

<?php
if ( 'edit' === $tab->get_slug() ) {
	$this->template( 'fields/schedule', array( 'record' => $record, 'origin' => $origin_slug, 'aggregator_action' => $aggregator_action ) );
}
?>

<?php include dirname( __FILE__ ) . '/refine.php'; ?>

<tr class="tribe-dependent" data-depends="#tribe-ea-field-ics_file" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
		<button type="button" class="button tribe-cancel">
			<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>

<tr class="tribe-dependent" data-depends="#tribe-ea-field-ical_file" data-condition-not-empty>
	<td colspan="2">
		<div class="tribe-ea-table-container">
			<span class='spinner tribe-ea-active'></span>
		</div>
	</td>
</tr>
