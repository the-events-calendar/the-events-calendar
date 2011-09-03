	<h3><?php _e('Customize Defaults', TribeEvents::PLUGIN_DOMAIN); ?></h3>
	<p><?php _e('These settings change the default event form. For example, if you set a default venue, this field will be automatically filled in on a new event.', TribeEvents::PLUGIN_DOMAIN) ?></p>
	<table class="form-table">
<tr>
			<th scope="row"><?php _e('Automatically replace empty fields with default values',TribeEvents::PLUGIN_DOMAIN); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Automatically replace empty fields with default values',TribeEvents::PLUGIN_DOMAIN); ?></span>
	                </legend>
	                <label title='Replace empty fields'>
	                    <input type="checkbox" name="defaultValueReplace" value="1" <?php checked( tribe_get_option('defaultValueReplace') ); ?> /> 
	                    <?php _e('Enabled',TribeEvents::PLUGIN_DOMAIN); ?>
	                </label>
	            </fieldset>
	        </td>
		</tr>
			<tr>
				<th scope="row"><?php _e('Default Organizer for Events', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><?php _e('Default Organizer', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><?php $tec->saved_organizers_dropdown(tribe_get_option('eventsDefaultOrganizerID'),'eventsDefaultOrganizerID');?><?php _e('The default organizer value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultOrganizerID') )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Default Venue for Events', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><?php _e('Default Venue', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><?php $tec->saved_venues_dropdown(tribe_get_option('eventsDefaultVenueID'),'eventsDefaultVenueID');?><?php _e('The default venue value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultVenueID') )  ?>
				</fieldset></td>
			</tr>
			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Address', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Address', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="eventsDefaultAddress" value="<?php echo tribe_get_option('eventsDefaultAddress') ?>" /> <?php _e('The default address value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultAddress') )  ?>
				</fieldset></td>
			</tr>
			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default City', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default City', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="eventsDefaultCity" value="<?php echo tribe_get_option('eventsDefaultCity') ?>" /> <?php _e('The default city value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultCity') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default State', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Province or State', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label>
						<select id="eventsDefaultState" name='eventsDefaultState'>
							<option value=""><?php _e('Select a State:',TribeEvents::PLUGIN_DOMAIN); ?></option>
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
						<?php _e('The default  value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultState') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Province', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Province or State', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="eventsDefaultProvince" value="<?php echo tribe_get_option('eventsDefaultProvince') ?>" /> <?php _e('The default  value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultProvince') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Postal Code', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Postal Code', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="eventsDefaultZip" value="<?php echo tribe_get_option('eventsDefaultZip') ?>" /> <?php _e('The default Postal Code value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultZip') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info">
			<th scope="row"><?php _e('Default Country for Events',TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td>
					<select name="defaultCountry" id="defaultCountry">
							<?php 
							$countries = TribeEventsViewHelpers::constructCountries();
							$defaultCountry = tribe_get_option('defaultCountry');
					foreach ($countries as $abbr => $fullname) {
						print ("<option value=\"$fullname\" ");
						if ($defaultCountry[1] == $fullname) { 
							print ('selected="selected" ');
						}
						print (">$fullname</option>\n");
					}
					?>
					</select>
				</td>
			</tr>
			<tr class="venue-default-info">
				<th scope="row"><?php _e('Default Phone', TribeEvents::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Phone', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="eventsDefaultPhone" value="<?php echo tribe_get_option('eventsDefaultPhone') ?>" /> <?php _e('The default phone value', TribeEvents::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', TribeEvents::PLUGIN_DOMAIN ), tribe_get_option('eventsDefaultPhone') )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Use a custom list of countries', TribeEvents::PLUGIN_DOMAIN ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Use the following list:', TribeEvents::PLUGIN_DOMAIN ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsCountries"><?php echo stripslashes(tribe_get_option('spEventsCountries'));?></textarea>
					<div><?php _e('One country per line in the following format: <br/>US, United States <br/> UK, United Kingdom.', TribeEvents::PLUGIN_DOMAIN);?> <?php _e('(Replaces the default list.)', TribeEvents::PLUGIN_DOMAIN) ?></div>
				</fieldset></td>
			</tr>
</table>

