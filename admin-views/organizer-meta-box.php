<?php
/**
 * Organizer metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<?php if ( empty( $hide_organizer_title ) ): ?>
	<tr class="organizer">
		<td><?php printf( __( '%s Name:', 'tribe-events-calendar' ), tribe_get_organizer_label_singular() ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Organizer]' size='25' value='<?php echo isset( $_OrganizerOrganizer ) ? esc_attr( $_OrganizerOrganizer ) : ""; ?>' />
		</td>
	</tr>
<?php endif; ?>
<tr class="organizer">
	<td><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerPhone' name='organizer[Phone]' size='25' value='<?php echo isset( $_OrganizerPhone ) ? esc_attr( $_OrganizerPhone ) : ""; ?>' />
	</td>
</tr>
<tr class="organizer">
	<td><?php _e( 'Website:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerWebsite' name='organizer[Website]' size='25' value='<?php echo isset( $_OrganizerWebsite ) ? esc_attr( $_OrganizerWebsite ) : ""; ?>' />
	</td>
</tr>
<tr class="organizer">
	<td><?php _e( 'Email:', 'tribe-events-calendar' ); ?>
		<small><?php _e( 'You may want to consider <a href="http://wordpress.org/plugins/tags/obfuscate">obfuscating</a> any e-mail address published on your site to best avoid it getting harvested by spammers.', 'tribe-events-calendar' ); ?></small>
	</td>
	<td class="organizer-email">
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerEmail' name='organizer[Email]' size='25' value='<?php echo isset( $_OrganizerEmail ) ? esc_attr( $_OrganizerEmail ) : ""; ?>' />
	</td>
</tr>

<script type="text/javascript">
	jQuery('[name=organizer\\[Organizer\\]]').blur(function () {
		jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',
			{
				action: 'tribe_event_validation',
				nonce : '<?php echo wp_create_nonce('tribe-validation-nonce'); ?>',
				type  : 'organizer',
				name  : jQuery('[name=organizer\\[Organizer\\]]').get(0).value
			},
			function (result) {
				if (result == 1) {
					jQuery('[name=organizer\\[Organizer\\]]').parent().removeClass('invalid').addClass('valid');
				} else {
					jQuery('[name=organizer\\[Organizer\\]]').parent().removeClass('valid').addClass('invalid');
				}
			}
		);
	});
</script>