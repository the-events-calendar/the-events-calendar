<?php
$field              = (object) array();
$field->label       = __( 'Import Type', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __( 'One-time imports include all currently listed events, while scheduled imports automatically grab new events and updates from Facebook on a set schedule. Single events can be added via a one-time import.', 'the-events-calendar' );
$field->source      = 'facebook_import_type';

$frequency              = (object) array();
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __( 'Select how often you would like events to be automatically imported.', 'the-events-calendar' );
$frequency->source      = 'facebook_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="facebook">
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<select
			name="aggregator[facebook][import_type]"
			id="tribe-ea-field-facebook_import_type"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			data-hide-search
		>
			<option value=""></option>
			<option value="manual">One-Time Import</option>
			<option value="auto">Scheduled Import</option>
		</select>
		<select
			name="aggregator[facebook][import_frequency]"
			id="tribe-ea-field-facebook_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition="auto"
		>
			<option value=""></option>
			<?php foreach ( $frequencies as $frequency_object ) : ?>
				<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( 'daily', $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
			<?php endforeach; ?>
		</select>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition-not="auto"
			data-condition-empty
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
			data-depends="#tribe-ea-field-facebook_import_type"
			data-condition="auto"
		></span>
	</td>
</tr>

<?php
$field              = (object) array();
$field->label       = __( 'URL', 'the-events-calendar' );
$field->placeholder = __( 'facebook.com/example', 'the-events-calendar' );
$field->help        = __( 'Enter the url for a Facebook group, page, or individual. Or, enter the url of a single Facebook event.', 'the-events-calendar' );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-facebook_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[facebook][source]"
			type="text"
			id="tribe-ea-field-facebook_source"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>"></span>
	</td>
</tr>

<tr class="tribe-dependent" data-depends="tribe-ea-field-facebook_import_type" data-condition-not-empty>
	<td colspan="2">
		<div class="tribe-ea-table-container">
			<span class='spinner tribe-ea-active'></span>
		</div>
	</td>
</tr>
