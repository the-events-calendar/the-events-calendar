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
<tr class="venue">
	<td><?php _e('Country:',$this->pluginDomain); ?></td>
	<td>
		<select tabindex="<?php $this->tabIndex(); ?>" name='venue[Country]' id="EventCountry">
			<?php
			$this->constructCountries( $postId );
			$defaultCountry = sp_get_option('defaultCountry');
			$current = ($_VenueCountry) ? $_VenueCountry : $defaultCountry[1];

			foreach ($this->countries as $abbr => $fullname) {
				echo '<option label="' . $abbr . '" value="' . $fullname . '" ';

				if($abbr == '')
					echo "disabled='disabled' ";

				if ($current == $fullname) {
					echo 'selected="selected" ';
				}
				echo '>' . $fullname . '</option>';
			}
			?>
		</select>
	</td>
</tr>
<tr class="venue">
	<?php if(!isset($_VenueStateProvince)) $_VenueStateProvince = ""; ?>
	<td><?php _e('State or Province:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" id="StateProvinceText" name="venue[Province]" class="tec_hide" type='text' name='' size='25' value='<?php echo $_VenueStateProvince != -1 ? $_VenueStateProvince : $_VenueProvince; ?>' />
	<select tabindex="<?php $this->tabIndex(); ?>" id="StateProvinceSelect" name="venue[State]" class="tec_hide" name=''>
		<option value=""><?php _e('Select a State:',$this->pluginDomain); ?></option>
		<?php
			foreach ($this->states as $abbr => $fullname) {
				print ("<option value=\"$abbr\" ");
				if (($_VenueStateProvince != -1 ? $_VenueStateProvince : $_VenueState) == $abbr) {
					print ('selected="selected" ');
				}
				print (">$fullname</option>\n");
			}
		?>
	</select>
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

<script type="text/javascript">

	jQuery(document).ready(function($) { 
		jQuery('[name=venue\\[Venue\\]]').blur(function(){

		jQuery.ajax(
			{
				type: 'POST', 
				url: '<?php echo $this->pluginUrl?>events-validator.class.php', data: 'type=venue&validation_nonce=<?php echo wp_create_nonce('venue-validation-nonce');?>&validate_name='+jQuery('[name=venue\\[Venue\\]]').get(0).value,
				success:function(r){
					if(r == 1){
						jQuery('[name=venue\\[Venue\\]]').parent().removeClass('invalid').addClass('valid');
					}else{
						jQuery('[name=venue\\[Venue\\]]').parent().removeClass('valid').addClass('invalid');
					}
					}, 
				async:false 
			});
	})});
</script>