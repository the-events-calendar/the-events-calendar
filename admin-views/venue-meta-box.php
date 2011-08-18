<?php
/**
* Venue metabox
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<tr class="venue">
	<td><?php _e('Venue Name:',$this->pluginDomain); ?></td>
	<td>
		<input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[Venue]' size='25'  value='<?php echo esc_attr($_VenueVenue); ?>' />
	</td>
</tr>
<tr class="venue">
	<td><?php _e('Address:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[Address]' size='25' value='<?php echo esc_attr($_VenueAddress); ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e('City:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='venue[City]' size='25' value='<?php echo esc_attr($_VenueCity); ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e('Country:',$this->pluginDomain); ?></td>
	<td>
		<select tabindex="<?php $this->tabIndex(); ?>" name='venue[Country]' id="EventCountry">
			<?php
			$countries = Tribe_View_Helpers::constructCountries( $postId );
			$defaultCountry = tribe_get_option('defaultCountry');
			$current = ($_VenueCountry) ? $_VenueCountry : $defaultCountry[1];

			foreach ($countries as $abbr => $fullname) {
				echo '<option label="' . esc_attr($abbr) . '" value="' . esc_attr($fullname) . '" ';

				if($abbr == '')
					echo "disabled='disabled' ";

				if ($current == $fullname) {
					echo 'selected="selected" ';
				}
				echo '>' . esc_html($fullname) . '</option>';
			}
			?>
		</select>
	</td>
</tr>
<tr class="venue">
	<?php if(!isset($_VenueStateProvince)) $_VenueStateProvince = ""; ?>
	<td><?php _e('State or Province:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" id="StateProvinceText" name="venue[Province]" class="tec_hide" type='text' name='' size='25' value='<?php echo $_VenueStateProvince != -1 ? esc_attr($_VenueStateProvince) : esc_attr($_VenueProvince); ?>' />
	<select tabindex="<?php $this->tabIndex(); ?>" id="StateProvinceSelect" name="venue[State]" class="tec_hide" name=''>
		<option value=""><?php _e('Select a State:',$this->pluginDomain); ?></option>
		<?php
			foreach (Tribe_View_Helpers::loadStates() as $abbr => $fullname) {
				print ("<option value=\"$abbr\" ");
				if (($_VenueStateProvince != -1 ? $_VenueStateProvince : $_VenueState) == $abbr) {
					print ('selected="selected" ');
				}
				print (">" . esc_html($fullname) . "</option>\n");
			}
		?>
	</select>
	</td>
</tr>
<tr class="venue">
	<td><?php _e('Postal Code:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventZip' name='venue[Zip]' size='6' value='<?php echo esc_attr($_VenueZip); ?>' /></td>
</tr>
<tr class="venue">
	<td><?php _e('Phone:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventPhone' name='venue[Phone]' size='14' value='<?php echo esc_attr($_VenuePhone); ?>' /></td>
</tr>

<script type="text/javascript">
	jQuery('[name=venue\\[Venue\\]]').blur(function(){
		jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',
			{
				action: 'tribe_event_validation',
				nonce: '<?php echo wp_create_nonce('tribe-validation-nonce'); ?>',
				type: 'venue',
				name: jQuery('[name=venue\\[Venue\\]]').get(0).value
			},
			function(result) {
				if (result == 1) {
					jQuery('[name=venue\\[Venue\\]]').parent().removeClass('invalid').addClass('valid');
				} else {
					jQuery('[name=venue\\[Venue\\]]').parent().removeClass('valid').addClass('invalid');
				}
			}
		);
	});
</script>