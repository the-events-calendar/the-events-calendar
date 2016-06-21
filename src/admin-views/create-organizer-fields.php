<tr class="linked-post organizer">
	<td><?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Phone][]' class='organizer-phone' size='25' value='' />
	</td>
</tr>
<tr class="linked-post organizer">
	<td><?php esc_html_e( 'Website:', 'the-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Website][]' class='organizer-website' size='25' value='' />
	</td>
</tr>
<tr class="linked-post organizer">
	<td>
		<?php esc_html_e( 'Email:', 'the-events-calendar' ); ?>
		<small><?php esc_html_e( 'The e-mail address will be obfuscated on your site to avoid it getting harvested by spammers.', 'the-events-calendar' ); ?></small>
	</td>
	<td class="organizer-email">
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Email][]' class='organizer-email' size='25' value='' />
	</td>
</tr>
