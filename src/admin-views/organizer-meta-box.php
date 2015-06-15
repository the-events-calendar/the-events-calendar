<?php
/**
 * Organizer metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<?php do_action( 'tribe_events_organizer_before_metabox', $post ); ?>
<?php if ( empty( $hide_organizer_title ) ): ?>
	<tr class="organizer">
		<td><?php printf( __( '%s Name:', 'tribe-events-calendar' ), tribe_get_organizer_label_singular() ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Organizer]' size='25' value='<?php echo isset( $_OrganizerOrganizer ) ? esc_attr( $_OrganizerOrganizer ) : ''; ?>' />
		</td>
	</tr>
<?php endif; ?>
<tr class="organizer">
	<td><?php _e( 'Phone:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerPhone' name='organizer[Phone]' size='25' value='<?php echo isset( $_OrganizerPhone ) ? esc_attr( $_OrganizerPhone ) : ''; ?>' />
	</td>
</tr>
<tr class="organizer">
	<td><?php _e( 'Website:', 'tribe-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerWebsite' name='organizer[Website]' size='25' value='<?php echo isset( $_OrganizerWebsite ) ? esc_attr( $_OrganizerWebsite ) : ''; ?>' />
	</td>
</tr>
<tr class="organizer">
	<td><?php _e( 'Email:', 'tribe-events-calendar' ); ?>
		<small><?php _e( 'You may want to consider <a href="http://wordpress.org/plugins/tags/obfuscate">obfuscating</a> any e-mail address published on your site to best avoid it getting harvested by spammers.', 'tribe-events-calendar' ); ?></small>
	</td>
	<td class="organizer-email">
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerEmail' name='organizer[Email]' size='25' value='<?php echo isset( $_OrganizerEmail ) ? esc_attr( $_OrganizerEmail ) : ''; ?>' />
	</td>
</tr>
<?php do_action( 'tribe_events_organizer_after_metabox', $post ); ?>

<script type="text/javascript">
	jQuery('[name=organizer\\[Organizer\\]]').blur(function () {
		jQuery.post('<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>',
			{
				action: 'tribe_event_validation',
				nonce : <?php echo json_encode( wp_create_nonce( 'tribe-validation-nonce' ) ); ?>,
				type  : 'organizer',
				name  : jQuery('[name=organizer\\[Organizer\\]]').get(0).value
			},
			function (result) {
				if (result == 1) {
					jQuery('.tribe-organizer-error').remove();
				} else {
					jQuery('.tribe-organizer-error').remove();
					jQuery( '[name=organizer\\[Organizer\\]]' ).after('<div class="tribe-organizer-error error form-invalid"><?php printf( __( '%s Name Already Exists', 'tribe-events-calendar' ), tribe_get_organizer_label_singular() ); ?></div>');
				}
			}
		);
	});
</script>
