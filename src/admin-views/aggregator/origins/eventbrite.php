<?php
$field = new stdClass;
$field->label = __( 'Eventbrite Event', 'the-events-calendar' );
$field->placeholder = __( 'Select from your existing Eventbrite events', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="eventbrite">
	<th scope="row">
		<label for="tribe-ea-field-eventbrite_selected_id"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<?php wp_nonce_field( 'import_eventbrite', 'import_eventbrite' ); ?>
		<input
			name="eventbrite_selected_id"
			type="hidden"
			id="select-eventbrite-existing"
			class="tribe-ea-field tribe-ea-size-xlarge"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<br><?php echo esc_html__( 'or', 'the-events-calender' ); ?><br>
		<?php
		$field = new stdClass;
		$field->placeholder = __( 'Eventbrite URL', 'the-events-calendar' );
		$field->help = __( 'Enter an Eventbrite event URL, e.g. https://www.eventbrite.com/e/example-12345', 'the-events-calendar' );
		?>
		<input
			name="eventbrite_id"
			type="text"
			id="eventbrite_id"
			class="tribe-ea-field tribe-ea-size-xlarge"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>
