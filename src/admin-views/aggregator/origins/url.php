<?php
$tab                = $this->tabs->get_active();
$origin_slug        = 'url';
$field              = (object) array();
$field->label       = __( 'Import Type:', 'the-events-calendar' );
$field->placeholder = __( 'Select Import Type', 'the-events-calendar' );
$field->help        = __( 'One-time imports include currently listed upcoming events, while scheduled imports automatically grab new events and updates from this url on a set schedule.', 'the-events-calendar' );
$field->source      = 'url_import_type';

$frequency              = (object) array();
$frequency->placeholder = __( 'Select Frequency', 'the-events-calendar' );
$frequency->help        = __( 'Select how often you would like events to be automatically imported.', 'the-events-calendar' );
$frequency->source      = 'url_import_frequency';

$cron = Tribe__Events__Aggregator__Cron::instance();
$frequencies = $cron->get_frequency();
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition="url">
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<?php if ( 'edit' === $aggregator_action ) : ?>
			<input type="hidden" name="aggregator[url][import_type]" id="tribe-ea-field-url_import_type" value="schedule" />
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></strong>
		<?php else : ?>
			<select
				name="aggregator[url][import_type]"
				id="tribe-ea-field-url_import_type"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-import-type"
				placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
				data-hide-search
				data-prevent-clear
			>
				<option value=""></option>
				<option value="manual"><?php echo esc_html__( 'One-Time Import', 'the-events-calendar' ); ?></option>
				<option value="schedule"><?php echo esc_html__( 'Scheduled Import', 'the-events-calendar' ); ?></option>
			</select>
		<?php endif; ?>

		<select
			name="aggregator[url][import_frequency]"
			id="tribe-ea-field-url_import_frequency"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large tribe-dependent"
			placeholder="<?php echo esc_attr( $frequency->placeholder ); ?>"
			data-hide-search
			data-depends="#tribe-ea-field-url_import_type"
			data-condition="schedule"
			data-prevent-clear
		>
			<option value=""></option>
			<?php foreach ( $frequencies as $frequency_object ) : ?>
				<option value="<?php echo esc_attr( $frequency_object->id ); ?>" <?php selected( empty( $record->meta['frequency'] ) ? 'daily' : $record->meta['frequency'], $frequency_object->id ); ?>><?php echo esc_html( $frequency_object->text ); ?></option>
			<?php endforeach; ?>
		</select>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $field->help ); ?>"
			data-depends="#tribe-ea-field-url_import_type"
			data-condition-not="schedule"
			data-condition-empty
			data-width-rule="all-triggers"
		></span>
		<span
			class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help tribe-dependent"
			data-bumpdown="<?php echo esc_attr( $frequency->help ); ?>"
			data-depends="#tribe-ea-field-url_import_type"
			data-condition="schedule"
			data-width-rule="all-triggers"
		></span>
	</td>
</tr>

<?php
if ( 'edit' === $tab->get_slug() ) {
	$this->template( 'fields/schedule', array( 'record' => $record, 'origin' => $origin_slug, 'aggregator_action' => $aggregator_action ) );
}
?>

<?php
$field              = (object) array();
$field->label       = __( 'URL:', 'the-events-calendar' );
$field->placeholder = __( 'example.com/', 'the-events-calendar' );
$field->help        = __( 'Enter the url for the calendar, website, or event you would like to import. Event Aggregator will attempt to import events at that location.', 'the-events-calendar' );

$range_option = tribe_get_option( 'tribe_aggregator_default_url_import_range', 30 * DAY_IN_SECONDS );
$range_strings = tribe( 'events-aggregator.settings' )->get_url_import_range_options( false );
$range_string = $range_strings[ $range_option ];
$range_message = esc_html( sprintf( __( 'Event Aggregator will try to fetch events starting within the next %s from the current date or the specified date;', 'the-events-calendar' ), $range_string ) );
$link = esc_attr( admin_url( '/edit.php?post_type=tribe_events&page=tribe-common&tab=imports#tribe-field-tribe_aggregator_default_url_import_range' ) );
$field->range_message = $range_message . ' ' . sprintf( '<a href="%s" target="_blank">%s</a> ', $link, esc_html__( 'you can modify this setting here.', 'the-events-calendar' ) );
?>
<tr class="tribe-dependent" data-depends="#tribe-ea-field-url_import_type" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-file"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[url][source]"
			type="text"
			id="tribe-ea-field-url_source"
			class="tribe-ea-field tribe-ea-size-xlarge"
			placeholder="<?php echo esc_attr( $field->placeholder ); ?>"
			value="<?php echo esc_attr( empty( $record->meta['source'] ) ? '' : $record->meta['source'] ); ?>"
		>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $field->help ); ?>" data-width-rule="all-triggers"></span>
	</td>
</tr>

<?php include dirname( __FILE__ ) . '/refine.php'; ?>

<div class="tribe-dependent" data-depends="#tribe-ea-field-url_import_type" data-condition-not-empty>
    <p><?php echo $field->range_message; ?></p>
</div>

<tr class="tribe-dependent" data-depends="#tribe-ea-field-url_import_type" data-condition-not-empty>
	<td colspan="2" class="tribe-button-row">
		<button type="submit" class="button button-primary tribe-preview">
			<?php esc_html_e( 'Preview', 'the-events-calendar' ); ?>
		</button>
		<button type="button" class="button tribe-cancel">
			<?php esc_html_e( 'Cancel', 'the-events-calendar' ); ?>
		</button>
	</td>
</tr>
