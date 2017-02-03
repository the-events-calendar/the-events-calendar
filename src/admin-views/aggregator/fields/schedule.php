<?php
$field              = (object) array();
$field->label       = __( 'Schedule:', 'the-events-calendar' );
$week_days = array();
for ( $i = 1; $i <= 7; $i++ ) {
	$week_days[] = array(
		'id' => $i,
		'text' => (string) date_i18n( 'l', strtotime( '31-12-2016 +' . $i . " day" ) ),
	);
}
$month_days = array();
for ( $i = 1; $i <= 31; $i++ ) {
	$month_days[] = array(
		'id' => $i,
		'text' => (string) $i
	);
}
?>
<tr
	class="tribe-dependent"
	data-depends="#tribe-ea-field-<?php echo esc_attr( $origin ) ?>_import_frequency"
	data-condition='["daily", "weekly", "monthly"]'
>
	<th scope="row">
		<label for="tribe-ea-field-import_type"><?php echo esc_html( $field->label ); ?></label>
	</th>
	<td>
		<span
			data-depends="#tribe-ea-field-<?php echo esc_attr( $origin ) ?>_import_frequency"
			data-condition="daily"
		>
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Import runs daily at approximately', 'the-events-calendar' ) ?></strong>
		</span>
		<span
			data-depends="#tribe-ea-field-<?php echo esc_attr( $origin ) ?>_import_frequency"
			data-condition="weekly"
		>
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Import runs weekly on', 'the-events-calendar' ) ?></strong>
			<input
				type="hidden"
				name="aggregator[<?php echo esc_attr( $origin ) ?>][schedule_day]"
				id="tribe-ea-field-<?php echo esc_attr( $origin ) ?>_schedule_day"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-medium tribe-field-inline-dropdown"
				placeholder="<?php echo esc_attr__( 'Day', 'the-events-calendar' ); ?>"
				data-hide-search
				data-prevent-clear
				data-options="<?php echo esc_attr( json_encode( $week_days ) ); ?>"
				value="<?php echo esc_attr( empty( $record->meta['schedule_day'] ) || $record->meta['schedule_day'] > 7 ? date( 'w', strtotime( $record->post->post_modified ) ) + 1 : $record->meta['schedule_day'] ); ?>"
			>
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'at approximately', 'the-events-calendar' ) ?></strong>
		</span>
		<span
			data-depends="#tribe-ea-field-<?php echo esc_attr( $origin ) ?>_import_frequency"
			data-condition="monthly"
		>
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'Import runs monthly on day', 'the-events-calendar' ) ?></strong>
			<input
				type="hidden"
				name="aggregator[<?php echo esc_attr( $origin ) ?>][schedule_day]"
				id="tribe-ea-field-<?php echo esc_attr( $origin ) ?>_schedule_day"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-tiny tribe-field-inline-dropdown"
				placeholder="<?php echo esc_attr__( 'Day', 'the-events-calendar' ); ?>"
				data-hide-search
				data-prevent-clear
				data-options="<?php echo esc_attr( json_encode( $month_days ) ); ?>"
				value="<?php echo esc_attr( empty( $record->meta['schedule_day'] ) ? date( 'j', strtotime( $record->post->post_modified ) ) : $record->meta['schedule_day'] ); ?>"
			>
			<strong class="tribe-ea-field-readonly"><?php echo esc_html__( 'at approximately', 'the-events-calendar' ) ?></strong>
		</span>
		<input
			autocomplete="off"
			type="text"
			class="tribe-timepicker tribe-ea-size-tiny"
			name="aggregator[<?php echo esc_attr( $origin ) ?>][schedule_time]"
			id="tribe-ea-field-<?php echo esc_attr( $origin ) ?>_schedule_time"
			<?php echo Tribe__View_Helpers::is_24hr_format() ? 'data-format="H:i"' : '' ?>"
			value="<?php echo esc_attr( empty( $record->meta['schedule_time'] ) ? Tribe__Date_Utils::time_only( strtotime( $record->post->post_modified ) ) : $record->meta['schedule_time'] ); ?>"
		/>
		<span class="helper-text hide-if-js"><?php esc_html_e( 'HH:MM', 'the-events-calendar' ) ?></span>
	</td>
</tr>