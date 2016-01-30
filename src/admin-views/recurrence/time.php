<div class="recurrence-row recurrence-time">
	<span class="timeofdayoptions">
		<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][start-time][hour]" data-field="custom-start-time-hour">
			{{#tribe_recurrence_select custom.[start-time].hour}}
				<?php echo $start_hour_options; ?>
			{{/tribe_recurrence_select}}
		</select>
		<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][start-time][minute]" data-field="custom-start-time-minute">
			{{#tribe_recurrence_select custom.[start-time].minute}}
				<?php echo $start_minute_options; ?>
			{{/tribe_recurrence_select}}
		</select>
		<?php if ( ! Tribe__View_Helpers::is_24hr_format() ) : ?>
			<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][start-time][meridian]" data-field="custom-start-time-meridian">
				{{#tribe_recurrence_select custom.[start-time].meridian}}
					<?php echo $start_meridian_options; ?>
				{{/tribe_recurrence_select}}
			</select>
		<?php endif; ?>
	</span>

	<span class="eventduration-preamble">
		<?php echo esc_html_x( 'and will run for:', 'custom recurrence duration', 'tribe-events-calendar-pro' ); ?>
	</span>

	<span class="eventduration">

		<input type="number"
			   min="0"
			   max="365"
			   step="1"
			   tabindex="<?php tribe_events_tab_index(); ?>"
			   name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][duration][days]"
			   data-field="custom-duration-days"
			   value="{{custom.duration.days}}"
			/>
		<?php echo esc_html_x( 'days', 'custom recurrence duration', 'tribe-events-calendar-pro' ); ?>

		<input type="number"
			   min="0"
			   max="23"
			   step="1"
			   tabindex="<?php tribe_events_tab_index(); ?>"
			   name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][duration][hours]"
			   data-field="custom-duration-hours"
			   value="{{custom.duration.hours}}"
			/>
		<?php echo esc_html_x( 'hours', 'custom recurrence duration', 'tribe-events-calendar-pro' ); ?>

		<input type="number"
			   min="0"
			   max="59"
			   step="1"
			   tabindex="<?php tribe_events_tab_index(); ?>"
			   name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][duration][minutes]"
			   data-field="custom-duration-minutes"
			   value="{{custom.duration.minutes}}"
			/>
		<?php echo esc_html_x( 'minutes', 'custom recurrence duration', 'tribe-events-calendar-pro' ); ?>
	</span>
</div>
