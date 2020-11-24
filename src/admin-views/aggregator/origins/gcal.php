<?php
$tab                = $this->tabs->get_active();
$origin_slug        = 'gcal';
$field              = (object) [];
$field->label       = __( 'Import Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __(
	'One-time imports include all events in the current feed, while scheduled imports automatically grab new events and updates from the feed on a set schedule.',
	'the-events-calendar'
);
$field->source      = 'gcal_import_type';

$frequency              = (object) [];
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __(
	'Select how often you would like events to be automatically imported.',
	'the-events-calendar'
);
$frequency->source      = 'gcal_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="gcal">
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<?php if ( 'edit' === $aggregator_action ) : ?>
			<input type="hidden" name="aggregator[gcal][import_type]" id="tribe-ea-field-gcal_import_type" value="schedule" />
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<select
				name="aggregator[gcal][import_type]"
				id="tribe-ea-field-gcal_import_type"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-import-type"
				placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
				data-hide-search
				data-prevent-clear
			>
				<option value="manual"><?php echo esc_html__( 'One-Time Import', 'the-events-calendar' ); ?></option>
				<option value="schedule"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></option>
			</select>
		<?php endif; ?>

		<span
			data-depends="#tribe-ea-field-gcal_import_type"
			data-condition="schedule"
		>
			<select
				name="aggregator[gcal][import_frequency]"
				id="tribe-ea-field-gcal_import_frequency"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
				placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
				data-hide-search
				data-prevent-clear
			>
				<?php foreach ( $frequencies as $frequency_object ) : ?>
					<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( empty( $record->meta['frequency'] ) ? 'daily' : $record->meta['frequency'], $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
				<?php endforeach; ?>
			</select>
			<span
				class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
				data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
				data-width-rule="all-triggers"
			></span>
		</span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-gcal_import_type"
			data-condition-not="schedule"
			data-condition-empty
			data-width-rule="all-triggers"
		></span>
	</td>
</tr>

<?php
if ( 'edit' === $tab->get_slug() ) {
	$this->template(
			'fields/schedule',
			[ 'record' => $record, 'origin' => $origin_slug, 'aggregator_action' => $aggregator_action ]
	);
}
?>

<?php
$field              = (object) [];
$field->label       = __( 'URL:', 'the-events-calendar' );
$field->placeholder = __( 'https://calendar.google.com/calendar/ical/example/basic.ics', 'the-events-calendar' );
$field->help        = __( 'Enter the url for the Google Calendar feed you wish to import.', 'the-events-calendar' );
$field->help       .= '<br/><br/>';
$field->help       .= __( 'You can find the url you need in your Google Calendar settings.', 'the-events-calendar' );
$field->help       .= '<ol>';
$field->help       .= '<li>' . __( 'Go to Settings &gt; Calendars and select the calendar you wish to import.', 'the-events-calendar' ) . '</li>';
$field->help       .= '<li>' . __( 'Scroll down to Calendar Address and click the iCal button (note: if your calendar is private, you\'ll need to click the iCal button next to the Private Address header instead).', 'the-events-calendar' ) . '</li>';
$field->help       .= '<li>' . __( 'Copy the provided url into this field to import the events into your WordPress site.', 'the-events-calendar' ) . '</li>';
$field->help       .= '</ol>';
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-gcal_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[gcal][source]"
			type="text"
			id="tribe-ea-field-gcal_source"
			class="tribe-ea-field tribe-ea-size-xlarge"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			value="<?php echo esc_attr( empty( $record->meta['source'] ) ? '' : $record->meta['source'] ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>

<?php include dirname( __FILE__ ) . '/refine.php'; ?>

<tr class="tribe-dependent" data-depends="#tribe-ea-field-gcal_import_type" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
		<button type="button" class="button tribe-cancel">
			<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>
