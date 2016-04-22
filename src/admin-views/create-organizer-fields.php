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
		<?php
		esc_html_e( 'Email:', 'the-events-calendar' );
		if ( apply_filters( 'tribe_show_organizer_email_obfuscation_alert', true ) ) {
			?>
			<small>
				<?php printf( esc_html__( 'You may want to consider %sobfuscating%s any e-mail address published on your site to best avoid it getting harvested by spammers.', 'the-events-calendar' ), '<a href="http://wordpress.org/plugins/tags/obfuscate">', '</a>' ); ?>
			</small>
			<?php
		}
		?>
	</td>
	<td class="organizer-email">
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Email][]' class='organizer-email' size='25' value='' />
	</td>
</tr>
