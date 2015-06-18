<h3><?php esc_html_e( 'Customize Defaults', 'tribe-events-calendar-pro' ); ?></h3>
<p><?php esc_html_e( 'These settings change the default event form. For example, if you set a default venue, this field will be automatically filled in on a new event.', 'tribe-events-calendar-pro' ) ?></p>
<table class="form-table">
	<tr>
		<th scope="row"><?php esc_html_e( 'Automatically replace empty fields with default values', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text">
					<span><?php esc_html_e( 'Automatically replace empty fields with default values', 'tribe-events-calendar-pro' ); ?></span>
				</legend>
				<label title='Replace empty fields'>
					<input type="checkbox" name="defaultValueReplace" value="1" <?php checked( tribe_get_option( 'defaultValueReplace' ) ); ?> />
					<?php esc_html_e( 'Enabled', 'tribe-events-calendar-pro' ); ?>
				</label>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Default Organizer for Events', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Organizer', 'tribe-events-calendar-pro' ); ?></legend>
				<label><?php $tecp->saved_organizers_dropdown( tribe_get_option( 'eventsDefaultOrganizerID' ), 'eventsDefaultOrganizerID' ); ?><?php esc_html_e( 'The default organizer value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultOrganizerID' ) ) ?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Default Venue for Events', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Venue', 'tribe-events-calendar-pro' ); ?></legend>
				<label><?php $tecp->saved_venues_dropdown( tribe_get_option( 'eventsDefaultVenueID' ), 'eventsDefaultVenueID' ); ?><?php esc_html_e( 'The default venue value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultVenueID' ) ) ?>
			</fieldset>
		</td>
	</tr>
	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default Address', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Address', 'tribe-events-calendar-pro' ); ?></legend>
				<label><input type="text" name="eventsDefaultAddress" value="<?php echo esc_attr( tribe_get_option( 'eventsDefaultAddress' ) ) ?>" /> <?php esc_html_e( 'The default address value', 'tribe-events-calendar-pro' ) ?>
				</label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultAddress' ) ) ?>
			</fieldset>
		</td>
	</tr>
	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default City', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default City', 'tribe-events-calendar-pro' ); ?></legend>
				<label><input type="text" name="eventsDefaultCity" value="<?php echo esc_attr( tribe_get_option( 'eventsDefaultCity' ) ) ?>" /> <?php esc_html_e( 'The default city value', 'tribe-events-calendar-pro' ) ?>
				</label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultCity' ) ) ?>
			</fieldset>
		</td>
	</tr>

	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default State', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Province or State', 'tribe-events-calendar-pro' ); ?></legend>
				<label>
					<select id="eventsDefaultState" name='eventsDefaultState'>
						<option value=""><?php esc_html_e( 'Select a State:', 'tribe-events-calendar-pro' ); ?></option>
						<?php
						foreach ( Tribe__Events__View_Helpers::loadStates() as $abbr => $fullname ) {
							?>
							<option value="<?php echo esc_attr( $abbr ); ?>" <?php selected( $abbr, tribe_get_option( 'eventsDefaultState' ) ); ?>><?php echo $fullname; ?></option>
							<?php
						}
						?>
					</select>
					<?php esc_html_e( 'The default  value', 'tribe-events-calendar-pro' ) ?>
				</label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultState' ) ) ?>
			</fieldset>
		</td>
	</tr>

	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default Province', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Province or State', 'tribe-events-calendar-pro' ); ?></legend>
				<label><input type="text" name="eventsDefaultProvince" value="<?php echo esc_attr( tribe_get_option( 'eventsDefaultProvince' ) ) ?>" /> <?php esc_html_e( 'The default  value', 'tribe-events-calendar-pro' ) ?>
				</label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultProvince' ) ) ?>
			</fieldset>
		</td>
	</tr>

	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default Postal Code', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Postal Code', 'tribe-events-calendar-pro' ); ?></legend>
				<label><input type="text" name="eventsDefaultZip" value="<?php echo esc_attr( tribe_get_option( 'eventsDefaultZip' ) ) ?>" /> <?php esc_html_e( 'The default Postal Code value', 'tribe-events-calendar-pro' ) ?>
				</label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultZip' ) ) ?>
			</fieldset>
		</td>
	</tr>

	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default Country for Events', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<select class="chosen" name="defaultCountry" id="defaultCountry">
				<?php
				$countries      = Tribe__Events__View_Helpers::constructCountries();
				$defaultCountry = tribe_get_option( 'defaultCountry' );
				foreach ( $countries as $abbr => $fullname ) {
					?>
					<option value="<?php echo esc_attr( $fullname ); ?>" <?php selected( $fullname, empty( $defaultCountry[1] ) ? '' : $defaultCountry[1] ); ?>><?php echo $fullname; ?></option>
					<?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr class="venue-default-info">
		<th scope="row"><?php esc_html_e( 'Default Phone', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Default Phone', 'tribe-events-calendar-pro' ); ?></legend>
				<label><input type="text" name="eventsDefaultPhone" value="<?php echo esc_attr( tribe_get_option( 'eventsDefaultPhone' ) ) ?>" /> <?php esc_html_e( 'The default phone value', 'tribe-events-calendar-pro' ) ?>
				</label><br /><?php printf( __( 'The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option( 'eventsDefaultPhone' ) ) ?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Use a custom list of countries', 'tribe-events-calendar-pro' ); ?></th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Use the following list:', 'tribe-events-calendar-pro' ); ?></legend>
				<textarea style="width:100%; height:100px;" name="tribeEventsCountries"><?php echo esc_textarea( tribe_get_option( 'tribeEventsCountries' ) ); ?></textarea>

				<div><?php _e( 'One country per line in the following format: <br/>US, United States <br/> UK, United Kingdom.', 'tribe-events-calendar-pro' ); ?> <?php esc_html_e( '(Replaces the default list.)', 'tribe-events-calendar-pro' ) ?></div>
			</fieldset>
		</td>
	</tr>
</table>
