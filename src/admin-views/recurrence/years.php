<div class="recurrence-row custom-recurrence-years">
	<table>
		<tr>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="1" {{tribe_checked_if_in '1' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 1, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="2" {{tribe_checked_if_in '2' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 2, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="3" {{tribe_checked_if_in '3' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 3, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="4" {{tribe_checked_if_in '4' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 4, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="5" {{tribe_checked_if_in '5' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 5, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="6" {{tribe_checked_if_in '6' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 6, 1, 2020 ) ) ?>
				</label>
			</td>
		</tr>
		<tr>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="7" {{tribe_checked_if_in '7' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 7, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="8" {{tribe_checked_if_in '8' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 8, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="9" {{tribe_checked_if_in '9' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 9, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="10" {{tribe_checked_if_in '10' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 10, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="11" {{tribe_checked_if_in '11' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 11, 1, 2020 ) ) ?>
				</label>
			</td>
			<td>
				<label>
					<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month][]" data-field="custom-year-month" value="12" {{tribe_checked_if_in '12' custom.year.month}}/>
					<?php echo date_i18n( 'M', mktime( 12, 0, 0, 12, 1, 2020 ) ) ?>
				</label>
			</td>
		</tr>
	</table>
	<div style="clear:both"></div>
	<div>
	<input type="checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][filter]" data-field="custom-year-filter" value="1" {{tribe_checked_if_is 1 custom.year.filter}}/>
		<?php _e( 'On the:', 'tribe-events-calendar-pro' ); ?>
		<select name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month-number]" data-field="custom-year-month-number">
			{{#tribe_recurrence_select custom.year.[month-number]}}
				<option value="1"><?php esc_html_e( 'First', 'tribe-events-calendar-pro' ); ?></option>
				<option value="2"><?php esc_html_e( 'Second', 'tribe-events-calendar-pro' ); ?></option>
				<option value="3"><?php esc_html_e( 'Third', 'tribe-events-calendar-pro' ); ?></option>
				<option value="4"><?php esc_html_e( 'Fourth', 'tribe-events-calendar-pro' ); ?></option>
				<option value="-1"><?php esc_html_e( 'Last', 'tribe-events-calendar-pro' ); ?></option>
			{{/tribe_recurrence_select}}
		</select>
		<select name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][month-day]" data-field="custom-year-month-day">
			{{#tribe_recurrence_select custom.year.[month-day]}}
				<option value="1"><?php esc_html_e( 'Monday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="2"><?php esc_html_e( 'Tuesday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="3"><?php esc_html_e( 'Wednesday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="4"><?php esc_html_e( 'Thursday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="5"><?php esc_html_e( 'Friday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="6"><?php esc_html_e( 'Saturday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="7"><?php esc_html_e( 'Sunday', 'tribe-events-calendar-pro' ); ?></option>
				<option value="-">--</option>
				<option value="-1"><?php esc_html_e( 'Day', 'tribe-events-calendar-pro' ); ?></option>
			{{/tribe_recurrence_select}}
		</select>

		<?php
		if ( 'rules' === $rule_type ) {
			?>
			<label class="tribe-custom-same-time">
				<input type="checkbox" class="tribe-same-time-checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][year][same-time]" data-field="custom-year-same-time" value="yes" {{tribe_checked_if_is 'yes' custom.year.[same-time]}}/> <?php esc_html_e( 'Same time', 'tribe-events-calendar-pro' ); ?>
			</label>
			<?php
		}
		?>
	</div>
</div>
