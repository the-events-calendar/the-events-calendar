<tr class="organizer">
	<td><?php _e('Organizer Name:',$this->pluginDomain); ?></td>
	<td>
		<input tabindex="<?php $this->tabIndex(); ?>" type='text' name='organizer[Organizer]' size='25'  value='<?php echo esc_html($_OrganizerOrganizer); ?>' />
	</td>
</tr>
<tr class="organizer">
	<td><?php _e('Phone:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='OrganizerPhone' name='organizer[Phone]' size='25' value='<?php echo $_OrganizerPhone; ?>' /></td>
</tr>
<tr class="organizer">
	<td><?php _e('Website:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='OrganizerWebsite' name='organizer[Website]' size='25' value='<?php echo $_OrganizerWebsite; ?>' /></td>
</tr>
<tr class="organizer">
	<td><?php _e('Email:',$this->pluginDomain); ?></td>
	<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='OrganizerEmail' name='organizer[Email]' size='25' value='<?php echo $_OrganizerEmail; ?>' /></td>
</tr>