<tr class="organizer">
	<td><?php _e('Organizer Name:',$this->pluginDomain); ?></td>
	<td>
		<input tabindex="<?php $this->tabIndex(); ?>" type='text' name='organizer[Organizer]' size='25'  value='<?php echo isset($_OrganizerOrganizer) ? esc_attr($_OrganizerOrganizer) : ""; ?>' />
	</td>
</tr>
<tr class="organizer">
	<td><?php _e('Phone:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='OrganizerPhone' name='organizer[Phone]' size='25' value='<?php echo isset($_OrganizerPhone) ? esc_attr($_OrganizerPhone) : ""; ?>' /></td>
</tr>
<tr class="organizer">
	<td><?php _e('Website:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='OrganizerWebsite' name='organizer[Website]' size='25' value='<?php echo isset($_OrganizerWebsite) ? esc_attr($_OrganizerWebsite) : ""; ?>' /></td>
</tr>
<tr class="organizer">
	<td><?php _e('Email:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='OrganizerEmail' name='organizer[Email]' size='25' value='<?php echo isset($_OrganizerEmail) ? esc_attr($_OrganizerEmail) : ""; ?>' /></td>
</tr>

<script type="text/javascript">

	jQuery(document).ready(function($) { 
		jQuery('[name=organizer\\[Organizer\\]]').blur(function(){

		jQuery.ajax(
			{
				type: 'POST', 
				url: '<?php echo $this->pluginUrl?>resources/events-validator.class.php', data: 'type=organizer&validation_nonce=<?php echo wp_create_nonce('organizer-validation-nonce');?>&validate_name='+jQuery('[name=organizer\\[Organizer\\]]').get(0).value,
				success:function(r){
					if(r == 1){
						jQuery('[name=organizer\\[Organizer\\]]').parent().removeClass('invalid').addClass('valid');
					}else{
						jQuery('[name=organizer\\[Organizer\\]]').parent().removeClass('valid').addClass('invalid');
					}
					}, 
				async:false 
			});
	})});
</script>