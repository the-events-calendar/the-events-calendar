<tr class="linked-post organizer">
	<td><label for="organizer-phone"><?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?></label></td>
	<td>
		<input id="organizer-phone" type='text' name='organizer[Phone][]' class='organizer-phone' size='25' value='' />
	</td>
</tr>
<tr class="linked-post organizer">
	<td><label for="organizer-website"><?php esc_html_e( 'Website:', 'the-events-calendar' ); ?></label></td>
	<td>
		<input id="organizer-website" type='text' name='organizer[Website][]' class='organizer-website' size='25' value='' />
	</td>
</tr>
<tr class="linked-post organizer">
	<td>
		<label for="organizer-email"><?php esc_html_e( 'Email:', 'the-events-calendar' ); ?></label>
	</td>
	<td class="organizer-email">
		<input id="organizer-email" type='text' name='organizer[Email][]' class='organizer-email' size='25' value='' />
		<small><?php esc_html_e( 'The e-mail address will be obfuscated on your site to avoid it getting harvested by spammers.', 'the-events-calendar' ); ?></small>
	</td>
</tr>
