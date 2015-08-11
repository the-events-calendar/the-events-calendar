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
		<?php if ( ! Tribe__Events__View_Helpers::is_24hr_format() ) : ?>
			<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][start-time][meridian]" data-field="custom-start-time-meridian">
				{{#tribe_recurrence_select custom.[start-time].meridian}}
					<?php echo $start_meridian_options; ?>
				{{/tribe_recurrence_select}}
			</select>
		<?php endif; ?>
	</span>
	to
	<span class="timeofdayoptions">
		<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][end-time][hour]" data-field="custom-end-time-hour">
			{{#tribe_recurrence_select custom.[end-time].hour}}
				<?php echo $end_hour_options; ?>
			{{/tribe_recurrence_select}}
		</select>
		<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][end-time][minute]" data-field="custom-end-time-minute">
			{{#tribe_recurrence_select custom.[end-time].minute}}
				<?php echo $end_minute_options; ?>
			{{/tribe_recurrence_select}}
		</select>
		<?php if ( ! Tribe__Events__View_Helpers::is_24hr_format() ) : ?>
			<select tabindex="<?php tribe_events_tab_index(); ?>" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][end-time][meridian]" data-field="custom-end-time-meridian">
				{{#tribe_recurrence_select custom.[end-time].meridian}}
					<?php echo $end_meridian_options; ?>
				{{/tribe_recurrence_select}}
			</select>
		<?php endif; ?>
	</span>
</div>
