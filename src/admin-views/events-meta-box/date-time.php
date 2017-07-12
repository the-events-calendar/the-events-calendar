<table cellspacing="0" cellpadding="0" id="EventInfo">
	<tr>
		<td colspan="2" class="tribe_sectionheader">
			<div class="tribe_sectionheader" style="">
				<h4><?php esc_html_e( 'Time &amp; Date', 'the-events-calendar' ); ?></h4></div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table class="eventtable">
				<tr id="recurrence-changed-row">
					<td colspan='2'><?php esc_html( sprintf( __( 'You have changed the recurrence rules of this %1$s.  Saving the %1$s will update all future %2$s.  If you did not mean to change all %2$s, then please refresh the page.', 'the-events-calendar' ), $events_label_singular_lowercase, $events_label_plural_lowercase ) ); ?></td>
				</tr>
				<tr>
					<td class="tribe-datetime-label"><?php esc_html_e( 'Start/End:', 'the-events-calendar' ); ?></td>
					<td class="tribe-datetime-block">
						<input
							autocomplete="off"
							tabindex="<?php tribe_events_tab_index(); ?>"
							type="text"
							class="tribe-datepicker tribe-field-start_date"
							name="EventStartDate"
							id="EventStartDate"
							value="<?php echo esc_attr( $EventStartDate ) ?>"
						/>
						<span class="helper-text hide-if-js"><?php esc_html_e( 'YYYY-MM-DD', 'the-events-calendar' ) ?></span>

						<input
							autocomplete="off"
							tabindex="<?php tribe_events_tab_index(); ?>"
							type="text"
							class="tribe-timepicker tribe-field-start_time"
							name="EventStartTime"
							id="EventStartTime"
							<?php echo Tribe__View_Helpers::is_24hr_format() ? 'data-format="H:i"' : '' ?>
							data-step="<?php echo esc_attr( $start_timepicker_step ); ?>"
							data-round="<?php echo esc_attr( $timepicker_round ); ?>"
							value="<?php echo esc_attr( $metabox->is_auto_draft() ? $start_timepicker_default : $EventStartTime ) ?>"
						/>
						<span class="helper-text hide-if-js"><?php esc_html_e( 'HH:MM', 'the-events-calendar' ) ?></span>

						<span class="tribe-datetime-separator"> <?php echo esc_html_x( 'to', 'Start Date Time "to" End Date Time', 'the-events-calendar' ); ?> </span>

						<input
							autocomplete="off"
							type="text"
							class="tribe-timepicker tribe-field-end_time"
							name="EventEndTime"
							id="EventEndTime"
							<?php echo Tribe__View_Helpers::is_24hr_format() ? 'data-format="H:i"' : '' ?>
							data-step="<?php echo esc_attr( $end_timepicker_step ); ?>"
							data-round="<?php echo esc_attr( $timepicker_round ); ?>"
							value="<?php echo esc_attr( $metabox->is_auto_draft() ? $end_timepicker_default : $EventEndTime ); ?>"
						/>
						<span class="helper-text hide-if-js"><?php esc_html_e( 'HH:MM', 'the-events-calendar' ) ?></span>

						<input
							autocomplete="off"
							type="text"
							class="tribe-datepicker tribe-field-end_date"
							name="EventEndDate"
							id="EventEndDate"
							value="<?php echo esc_attr( $EventEndDate ); ?>"
						/>
						<span class="helper-text hide-if-js"><?php esc_html_e( 'YYYY-MM-DD', 'the-events-calendar' ) ?></span>

						<select
							tabindex="<?php tribe_events_tab_index(); ?>"
							name="EventTimezone"
							id="event-timezone"
							class="tribe-field-timezone tribe-dropdown hide-if-js"
							data-timezone-label="<?php esc_attr_e( 'Timezone:', 'the-events-calendar' ) ?>"
							data-timezone-value="<?php echo esc_attr( Tribe__Events__Timezones::get_event_timezone_string() ) ?>"
						>
							<?php echo wp_timezone_choice( Tribe__Events__Timezones::get_event_timezone_string() ); ?>
						</select>

						<p class="tribe-allday">
							<input
								tabindex="<?php tribe_events_tab_index(); ?>"
								type="checkbox"
								id="allDayCheckbox"
								name="EventAllDay"
								value="yes"
								<?php echo esc_html( $isEventAllDay ); ?>
							/>
							<label for="allDayCheckbox"><?php esc_html_e( 'All Day Event', 'the-events-calendar' ); ?></label>
						</p>
					</td>
				</tr>
				<tr class="event-dynamic-helper">
					<td class="label">
					</td>
					<td>
						<div class="event-dynamic-helper-text"></div>
					</td>
				</tr>
				<?php
				/**
				 * Fires after the event end date field in The Events Calendar meta box
				 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
				 *
				 * @param int $event->ID the event currently being edited, will be 0 if creating a new event
				 * @param boolean
				 */
				do_action( 'tribe_events_date_display', $event->ID, true );
				?>
			</table>
		</td>
	</tr>
</table>