<tr class="linked-post organizer tribe-linked-type-organizer-phone">
	<td><label for="organizer-phone"><?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?></label></td>
	<td>
		<input id="organizer-phone" type='text' name='organizer[Phone][]' class='organizer-phone' size='25' value='' />
	</td>
</tr>
<tr class="linked-post organizer tribe-linked-type-organizer-website">
	<td><label for="organizer-website"><?php esc_html_e( 'Website:', 'the-events-calendar' ); ?></label></td>
	<td>
		<input id="organizer-website" type='url' name='organizer[Website][]' class='organizer-website' size='25' value='' />
	</td>
</tr>
<tr class="linked-post organizer tribe-linked-type-organizer-email">
	<td>
		<label for="organizer-email"><?php esc_html_e( 'Email:', 'the-events-calendar' ); ?></label>
	</td>
	<td class="organizer-email">
		<input id="organizer-email" type='text' name='organizer[Email][]' class='organizer-email' size='25' value='' />
		<p><?php esc_html_e( 'The e-mail address will be obfuscated on this site to avoid it getting harvested by spammers.', 'the-events-calendar' ); ?></p>
	</td>
</tr>
