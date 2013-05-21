	<h3><?php _e('Customize Defaults', 'tribe-events-calendar-pro'); ?></h3>
	<p><?php _e('These settings change the default event form. For example, if you set a default venue, this field will be automatically filled in on a new event.', 'tribe-events-calendar-pro') ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Automatically replace empty fields with default values','tribe-events-calendar-pro'); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Automatically replace empty fields with default values','tribe-events-calendar-pro'); ?></span>
	                </legend>
	                <label title='Replace empty fields'>
	                    <input type="checkbox" name="defaultValueReplace" value="1" <?php checked( tribe_get_option('defaultValueReplace') ); ?> /> 
	                    <?php _e('Enabled','tribe-events-calendar-pro'); ?>
	                </label>
	            </fieldset>
	        </td>
		</tr>
			<tr>
				<th scope="row"><?php _e('Default Organizer for Events', 'tribe-events-calendar-pro'); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><?php _e('Default Organizer', 'tribe-events-calendar-pro' ); ?></legend>
					<label><?php $tecp->saved_organizers_dropdown(tribe_get_option('eventsDefaultOrganizerID'),'eventsDefaultOrganizerID');?><?php _e('The default organizer value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultOrganizerID') )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Default Venue for Events', 'tribe-events-calendar-pro'); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><?php _e('Default Venue', 'tribe-events-calendar-pro' ); ?></legend>
					<label><?php $tecp->saved_venues_dropdown(tribe_get_option('eventsDefaultVenueID'),'eventsDefaultVenueID');?><?php _e('The default venue value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultVenueID') )  ?>
				</fieldset></td>
			</tr>
			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Address', 'tribe-events-calendar-pro'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Address', 'tribe-events-calendar-pro' ); ?></legend>
					<label><input type="text" name="eventsDefaultAddress" value="<?php echo esc_attr( tribe_get_option('eventsDefaultAddress') ) ?>" /> <?php _e('The default address value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultAddress') )  ?>
				</fieldset></td>
			</tr>
			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default City', 'tribe-events-calendar-pro'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default City', 'tribe-events-calendar-pro' ); ?></legend>
					<label><input type="text" name="eventsDefaultCity" value="<?php echo esc_attr( tribe_get_option('eventsDefaultCity') ) ?>" /> <?php _e('The default city value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultCity') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default State', 'tribe-events-calendar-pro'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Province or State', 'tribe-events-calendar-pro' ); ?></legend>
					<label>
						<select id="eventsDefaultState" name='eventsDefaultState'>
							<option value=""><?php _e('Select a State:','tribe-events-calendar-pro'); ?></option>
							<?php
								foreach (TribeEventsViewHelpers::loadStates() as $abbr => $fullname) {
									print ("<option value=\"$abbr\" ");
									if (tribe_get_option('eventsDefaultState') == $abbr) {
										print ('selected="selected" ');
									}
									print (">$fullname</option>\n");
								}
							?>
						</select>
						<?php _e('The default  value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultState') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Province', 'tribe-events-calendar-pro'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Province or State', 'tribe-events-calendar-pro' ); ?></legend>
					<label><input type="text" name="eventsDefaultProvince" value="<?php echo esc_attr( tribe_get_option('eventsDefaultProvince') ) ?>" /> <?php _e('The default  value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultProvince') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Postal Code', 'tribe-events-calendar-pro'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Postal Code', 'tribe-events-calendar-pro' ); ?></legend>
					<label><input type="text" name="eventsDefaultZip" value="<?php echo esc_attr( tribe_get_option('eventsDefaultZip') ) ?>" /> <?php _e('The default Postal Code value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultZip') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
			<th scope="row"><?php _e('Default Country for Events','tribe-events-calendar-pro'); ?></th>
				<td>
					<select class="chosen" name="defaultCountry" id="defaultCountry">
							<?php 
							$countries = TribeEventsViewHelpers::constructCountries();
							$defaultCountry = tribe_get_option('defaultCountry');
					foreach ($countries as $abbr => $fullname) {
						print ("<option value=\"$fullname\" ");
						if ( isset($defaultCountry[1]) && $defaultCountry[1] == $fullname) { 
							print ('selected="selected" ');
						}
						print (">$fullname</option>\n");
					}
					?>
					</select>
				</td>
			</tr>
			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Phone', 'tribe-events-calendar-pro'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Phone', 'tribe-events-calendar-pro' ); ?></legend>
					<label><input type="text" name="eventsDefaultPhone" value="<?php echo esc_attr( tribe_get_option('eventsDefaultPhone') ) ?>" /> <?php _e('The default phone value', 'tribe-events-calendar-pro' ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', 'tribe-events-calendar-pro' ), tribe_get_option('eventsDefaultPhone') )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Use a custom list of countries', 'tribe-events-calendar-pro' ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Use the following list:', 'tribe-events-calendar-pro' ); ?></legend>
					<textarea style="width:100%; height:100px;" name="tribeEventsCountries"><?php echo esc_textarea(tribe_get_option('tribeEventsCountries')); ?></textarea>
					<div><?php _e('One country per line in the following format: <br/>US, United States <br/> UK, United Kingdom.', 'tribe-events-calendar-pro');?> <?php _e('(Replaces the default list.)', 'tribe-events-calendar-pro') ?></div>
				</fieldset></td>
			</tr>
</table>