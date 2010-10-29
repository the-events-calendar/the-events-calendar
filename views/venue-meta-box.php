<tr class="venue">
	<td><?php _e('Venue Name:',$this->pluginDomain); ?></td>
	<td>
		<input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[Venue]' size='25'  value='<?php echo $_VenueVenue; ?>' />
	</td>
</tr>
<tr class="venue">
	<td><?php _e('Address:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[Address]' size='25' value='<?php echo $_VenueAddress; ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e('City:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[City]' size='25' value='<?php echo $_VenueCity; ?>' /></td>
</tr>
<input name='venue[StateExists]' type="hidden" value="<?php echo ($_VenueCountry !== 'United States') ? 0 : 1; ?>">
<tr id="International" class=" venue <?php if($_VenueCountry == 'United States' || $_VenueCountry == '' ) echo('tec_hide'); ?>">
	<td><?php _e('Province:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[Province]' size='10' value='<?php echo $_VenueProvince; ?>' /></td>
</tr>
<tr id="USA" class="venue <?php if($_VenueCountry !== 'United States') echo'tec_hide'; ?>">
	<td><?php _e('State:',$this->pluginDomain); ?></td>
	<td>
		<select tabindex="<?php $this->tabIndex(); ?>" name='venue[State]'>
			<option value=""><?php _e('Select a State:',$this->pluginDomain); ?></option> 
			<?php 
				foreach ($this->states as $abbr => $fullname) {
					print ("<option value=\"$abbr\" ");
					if ($_VenueState == $abbr) { 
						print ('selected="selected" '); 
					}
					print (">$fullname</option>\n");
				}
			?>
		</select>
	</td>
</tr>
<tr class="venue">
	<td><?php _e('Country:',$this->pluginDomain); ?></td>
	<td>
		<select tabindex="<?php $this->tabIndex(); ?>" name='venue[Country]' id="EventCountry">
			<?php
			$this->constructCountries( $postId );
			$defaultCountry = sp_get_option('defaultCountry');
			if( $_VenueCountry ) {
				foreach ($this->countries as $abbr => $fullname) {
					echo '<option label="' . $abbr . '" value="' . $fullname . '" ';
					if ($_VenueCountry == $fullname) {
						echo 'selected="selected" ';
						$eventCountryLabel = $abbr;
					}
					echo '>' . $fullname . '</option>';
			}
			} elseif( $defaultCountry && !get_post_custom_keys( $postId ) ) {
				foreach ($this->countries as $abbr => $fullname) {
					echo '<option label="' . $abbr . '" value="' . $fullname . '" ';
					if ($defaultCountry[1] == $fullname) {
						echo 'selected="selected" ';
						$eventCountryLabel = $abbr;
					}
					echo '>' . $fullname . '</option>';
			}
			} else {
				$eventCountryLabel = "";
				foreach ($this->countries as $abbr => $fullname) {
					echo '<option label="' . $abbr . '" value="' . $fullname . '" >' . $fullname . '</option>';
			}
			}
			?>
		</select>
			<input name='venue[CountryLabel]' type="hidden" value="<?php echo $eventCountryLabel; ?>" />
	</td>
</tr>
<tr class="venue">
	<td><?php _e('Postal Code:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventZip' name='venue[Zip]' size='6' value='<?php echo $_VenueZip; ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e('Phone:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventPhone' name='venue[Phone]' size='14' value='<?php echo $_VenuePhone; ?>' /></td>
</tr>