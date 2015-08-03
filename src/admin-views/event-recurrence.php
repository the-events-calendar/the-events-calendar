<?php
$start_hour_options = Tribe__Events__View_Helpers::getHourOptions( null, true );
$start_minute_options = Tribe__Events__View_Helpers::getMinuteOptions( null, true );
$start_meridian_options = Tribe__Events__View_Helpers::getMeridianOptions( null, true );

$end_hour_options = Tribe__Events__View_Helpers::getHourOptions( null, false );
$end_minute_options = Tribe__Events__View_Helpers::getMinuteOptions( null, false );
$end_meridian_options = Tribe__Events__View_Helpers::getMeridianOptions( null, false );
?>
<tr class="recurrence-row">
	<td class="recurrence-rules-header"><?php esc_html_e( 'Recurrence Rules:', 'tribe-events-calendar-pro' ); ?></td>
	<td>
		<div id="tribe-recurrence-staging"></div>
		<script type="text/x-handlebars-template" id="tmpl-tribe-recurrence">
			<div class="tribe-event-recurrence tribe-event-recurrence-rule">
				<div class="tribe-handle" title="Click to toggle"></div>
				<input type="hidden" name="is_recurring[]" data-field="is_recurring" value="{{#if is_recurring}}true{{else}}false{{/if}}"/>
				<select name="recurrence[rules][][type]" data-field="type" data-single="<?php esc_attr_e( 'event', 'tribe-events-calendar-pro' ) ?>" data-plural="<?php esc_attr_e( 'events', 'tribe-events-calendar-pro' ) ?>">
					{{#tribe_recurrence_select type}}
						<option value="None"><?php esc_html_e( 'Once', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Every Day"><?php esc_html_e( 'Every Day', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Every Week"><?php esc_html_e( 'Every Week', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Every Month"><?php esc_html_e( 'Every Month', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Every Year"><?php esc_html_e( 'Every Year', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Custom"><?php esc_html_e( 'Custom', 'tribe-events-calendar-pro' ); ?></option>
					{{/tribe_recurrence_select}}
				</select>
				<span class="recurrence-end">
					<?php esc_html_e( 'and will end', 'tribe-events-calendar-pro' ); ?>
					<select name="recurrence[rules][][end-type]" data-field="end-type">
						{{#tribe_recurrence_select this.[end-type]}}
							<option value="On"><?php esc_html_e( 'On', 'tribe-events-calendar-pro' ); ?></option>
							<option value="After"><?php esc_html_e( 'After', 'tribe-events-calendar-pro' ); ?></option>
							<option value="Never"><?php esc_html_e( 'Never', 'tribe-events-calendar-pro' ); ?></option>
						{{/tribe_recurrence_select}}
					</select>
					<input autocomplete="off" placeholder="<?php echo esc_attr( Tribe__Events__Date_Utils::date_only( date( Tribe__Events__Date_Utils::DBDATEFORMAT ) ) ); ?>" type="text" class="tribe-datepicker recurrence_end tribe-no-end-date-update" name="recurrence[rules][][end]" data-field="end" value="{{end}}"/>
					<span class="rec-count">
						<input autocomplete="off" type="text" name="recurrence[rules][][end-count]" data-field="end-count" class="recurrence_end_count" value="{{this.[end-count]}}" />
						<span class='occurence-count-text'><?php _ex( 'events', 'occurence count text', 'tribe-events-calendar-pro' ) ?></span>
					</span>
					<span class="rec-error rec-end-error">
						<?php esc_html_e( 'You must select a recurrence end date', 'tribe-events-calendar-pro' ); ?>
					</span>
				</span>
				<div class="recurrence-rows">
					<div class="recurrence-row custom-recurrence-frequency">
						<?php esc_html_e( 'Frequency', 'tribe-events-calendar-pro' ); ?>
						<select name="recurrence[rules][][custom][type]" data-field="custom-type">
							{{#tribe_recurrence_select custom.type}}
								<option value="Daily" data-plural="<?php esc_attr_e( 'Day(s)', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=""><?php esc_html_e( 'Daily', 'tribe-events-calendar-pro' ); ?></option>
								<option value="Weekly" data-plural="<?php esc_attr_e( 'Week(s) on:', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=".custom-recurrence-weeks"><?php esc_html_e( 'Weekly', 'tribe-events-calendar-pro' ); ?></option>
								<option value="Monthly" data-plural="<?php esc_attr_e( 'Month(s) on the:', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=".custom-recurrence-months"><?php esc_html_e( 'Monthly', 'tribe-events-calendar-pro' ); ?></option>
								<option value="Yearly" data-plural="<?php esc_attr_e( 'Year(s) on:', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=".custom-recurrence-years"><?php esc_html_e( 'Yearly', 'tribe-events-calendar-pro' ); ?></option>
							{{/tribe_recurrence_select}}
						</select>
						<?php esc_html_e( 'Every', 'tribe-events-calendar-pro' ); ?>
						<input type="text" name="recurrence[rules][][custom][interval]" data-field="custom-interval" value="{{#if custom.interval}}{{custom.interval}}{{else}}1{{/if}}" />
						<span class="recurrence-interval-type"></span>
						<input type="hidden" name="recurrence[rules][][custom][type-text]" data-field="custom-type-text" value="{{custom.[type-text]}}" />
						<input type="hidden" name="recurrence[rules][][occurrence-count-text]" data-field="occurrence-count-text" value="<?php esc_attr_e( _x( 'events', 'occurence count text', 'tribe-events-calendar-pro' ) ) ?>" />
						<span class="rec-error rec-days-error"><?php esc_html_e( 'Frequency of recurring event must be a number', 'tribe-events-calendar-pro' ); ?></span>

						<label class="tribe-custom-same-time tribe-custom-same-time-day">
							<input type="checkbox" class="tribe-same-time-checkbox" name="recurrence[rules][][custom][day][same-time]" data-field="custom-day-same-time" value="yes" {{tribe_checked_if_is 'yes' custom.day.[same-time]}}/> <?php esc_html_e( 'Same time', 'tribe-events-calendar-pro' ); ?>
						</label>
					</div>
					<?php
					$rule_type = 'rules';

					include apply_filters( 'tribe_pro_recurrence_template_weeks', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/weeks.php', $rule_type );
					include apply_filters( 'tribe_pro_recurrence_template_months', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/months.php', $rule_type );
					include apply_filters( 'tribe_pro_recurrence_template_years', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/years.php', $rule_type );
					include apply_filters( 'tribe_pro_recurrence_template_time', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/time.php', $rule_type );
					?>
				</div>
				<div class="tribe-event-recurrence-description"></div>
			</div>
		</script>
		<button id="tribe-add-recurrence" class="button"><?php esc_html_e( 'Add Another Rule', 'tribe-events-calendar-pro' ); ?></button>
	</td>
</tr>
<tr class="recurrence-row tribe-recurrence-exclusion-row">
	<td class="recurrence-exclusions-header"><?php esc_html_e( 'Exclusions:', 'tribe-events-calendar-pro' ); ?></td>
	<td>
		<div id="tribe-exclusion-staging"></div>
		<script type="text/x-handlebars-template" id="tmpl-tribe-exclusion">
			<div class="tribe-event-recurrence tribe-event-recurrence-exclusion">
				<div class="tribe-handle" title="Click to toggle"></div>
				<select name="recurrence[exclusions][][custom][type]" data-field="custom-type" data-single="<?php esc_attr_e( 'event', 'tribe-events-calendar-pro' ) ?>" data-plural="<?php esc_attr_e( 'events', 'tribe-events-calendar-pro' ) ?>">
					{{#tribe_recurrence_select custom.type}}
						<option value="None"><?php esc_html_e( 'None', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Date"><?php esc_html_e( 'Date', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Daily" data-plural="<?php esc_attr_e( 'Day(s)', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=""><?php esc_html_e( 'Daily', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Weekly" data-plural="<?php esc_attr_e( 'Week(s) on:', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=".custom-recurrence-weeks"><?php esc_html_e( 'Weekly', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Monthly" data-plural="<?php esc_attr_e( 'Month(s) on the:', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=".custom-recurrence-months"><?php esc_html_e( 'Monthly', 'tribe-events-calendar-pro' ); ?></option>
						<option value="Yearly" data-plural="<?php esc_attr_e( 'Year(s) on:', 'tribe-events-calendar-pro' ); ?>" data-rule-segment=".custom-recurrence-years"><?php esc_html_e( 'Yearly', 'tribe-events-calendar-pro' ); ?></option>
					{{/tribe_recurrence_select}}
				</select>
				<span class="recurrence-end">
					<?php esc_html_e( 'will not occur on', 'tribe-events-calendar-pro' ); ?>
					<input autocomplete="off" placeholder="<?php echo esc_attr( Tribe__Events__Date_Utils::date_only( date( Tribe__Events__Date_Utils::DBDATEFORMAT ) ) ); ?>" type="text" class="tribe-datepicker custom-date" name="recurrence[exclusions][][custom][date][date]" data-field="custom-date" value="{{custom.date.date}}"/>
				</span>
				<span class="recurrence-row custom-recurrence-frequency">
					<?php esc_html_e( 'will not occur every', 'tribe-events-calendar-pro' ); ?>
					<input type="text" name="recurrence[exclusions][][custom][interval]" data-field="custom-interval" value="{{#if custom.interval}}{{custom.interval}}{{else}}1{{/if}}" />
					<span class="recurrence-interval-type"></span>
					<input type="hidden" name="recurrence[exclusions][][custom][type-text]" data-field="custom-type-text" value="{{custom.[type-text]}}" />
					<input type="hidden" name="recurrence[exclusions][][occurrence-count-text]" data-field="occurrence-count-text" value="<?php esc_attr_e( _x( 'events', 'occurence count text', 'tribe-events-calendar-pro' ) ) ?>" />
					<span class="rec-error rec-days-error"><?php esc_html_e( 'Frequency of recurring event must be a number', 'tribe-events-calendar-pro' ); ?></span>
				</span>
				<div class="recurrence-rows">
					<?php
					$rule_type = 'exclusions';

					include apply_filters( 'tribe_pro_recurrence_template_weeks', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/weeks.php', $rule_type );
					include apply_filters( 'tribe_pro_recurrence_template_months', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/months.php', $rule_type );
					include apply_filters( 'tribe_pro_recurrence_template_years', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/years.php', $rule_type );
					include apply_filters( 'tribe_pro_recurrence_template_time', Tribe__Events__Pro__Main::instance()->pluginPath . '/src/admin-views/recurrence/time.php', $rule_type );
					?>
				</div>
			</div>
		</script>
		<button id="tribe-add-exclusion" class="button"><?php esc_html_e( 'Add Exclusion', 'tribe-events-calendar-pro' ); ?></button>
	</td>
</tr>
