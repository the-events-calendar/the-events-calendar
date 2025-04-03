<?php
/**
 * Organizer metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<?php
/**
 * Fires above the organizer meta box in both the event editor and the single organizer editor in the admin
 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
 *
 * @param WP_Post $post if editing an event, the event currently being edited;
 *                      if editing an organizer, the organizer currently being edited
 */
do_action( 'tribe_events_organizer_before_metabox', $post );
?>
<?php if ( empty( $hide_organizer_title ) ): ?>
	<tr class="organizer tribe-linked-type-organizer-name">
		<td><?php printf( esc_html__( '%s Name:', 'the-events-calendar' ), tribe_get_organizer_label_singular() ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Organizer]' size='25' value='<?php echo isset( $organizer_title ) ? esc_attr( $organizer_title ) : ''; ?>' />
		</td>
	</tr>
<?php endif; ?>
<tr class="organizer tribe-linked-type-organizer-phone">
	<td><?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerPhone' name='organizer[Phone]' size='25' value='<?php echo isset( $_OrganizerPhone ) ? esc_attr( $_OrganizerPhone ) : ''; ?>' />
	</td>
</tr>
<tr class="organizer tribe-linked-type-organizer-website">
	<td><?php esc_html_e( 'Website:', 'the-events-calendar' ); ?></td>
	<td>
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='url' id='OrganizerWebsite' name='organizer[Website]' size='25' value='<?php echo isset( $_OrganizerWebsite ) ? esc_attr( $_OrganizerWebsite ) : ''; ?>' />
	</td>
</tr>
<tr class="organizer tribe-linked-type-organizer-email">
	<td><?php esc_html_e( 'Email:', 'the-events-calendar' ); ?>
	</td>
	<small><?php esc_html_e( 'The e-mail address will be obfuscated on this site to avoid it getting harvested by spammers.', 'the-events-calendar' ); ?></small>
	<td class="organizer-email">
		<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='OrganizerEmail' name='organizer[Email]' size='25' value='<?php echo isset( $_OrganizerEmail ) ? esc_attr( $_OrganizerEmail ) : ''; ?>' />
	</td>
</tr>
<?php
/**
 * Fires below the organizer meta box in both the event editor and the single organizer editor in the admin
 * HTML outputted here should be wrapped in a table row (<tr>) that contains 2 cells (<td>s)
 *
 * @param WP_Post $post if editing an event, the event currently being edited;
 *                      if editing an organizer, the organizer currently being edited
 */
do_action( 'tribe_events_organizer_after_metabox', $post );
?>

<script>
	jQuery('[name=organizer\\[Organizer\\]]').on( 'blur', function () {
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
					jQuery( '[name=organizer\\[Organizer\\]]' ).after('<div class="tribe-organizer-error error form-invalid"><?php printf( __( '%s Name Already Exists', 'the-events-calendar' ), tribe_get_organizer_label_singular() ); ?></div>');
				}
			}
		);
	});
</script>
