<div class="recurrence-row custom-recurrence-months">
	<div class="recurrence-month-on-the">
		<select name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][month][number]" data-field="custom-month-number">
			{{#tribe_recurrence_select custom.month.number}}
				<option value="First"><?php esc_html_e( 'First', 'tribe-events-calendar-pro' ); ?></option>
				<option value="Second"><?php esc_html_e( 'Second', 'tribe-events-calendar-pro' ); ?></option>
				<option value="Third"><?php esc_html_e( 'Third', 'tribe-events-calendar-pro' ); ?></option>
				<option value="Fourth"><?php esc_html_e( 'Fourth', 'tribe-events-calendar-pro' ); ?></option>
				<option value="Fifth"><?php esc_html_e( 'Fifth', 'tribe-events-calendar-pro' ); ?></option>
				<option value="Last"><?php esc_html_e( 'Last', 'tribe-events-calendar-pro' ); ?></option>
				<option value="">--</option>
				<?php for ( $i = 1; $i <= 31; $i ++ ): ?>
					<option value="<?php echo $i ?>"><?php echo $i; ?></option>
				<?php endfor; ?>
			{{/tribe_recurrence_select}}
		</select>
		<select name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][month][day]" data-field="custom-month-day">
			{{#tribe_recurrence_select custom.month.day}}
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
				<input type="checkbox" class="tribe-same-time-checkbox" name="recurrence[<?php echo esc_attr( $rule_type ); ?>][][custom][month][same-time]" data-field="custom-month-same-time" value="yes" {{tribe_checked_if_is 'yes' custom.month.[same-time]}}/> <?php esc_html_e( 'Same time', 'tribe-events-calendar-pro' ); ?>
			</label>
			<?php
		}
		?>
	</div>
</div>
