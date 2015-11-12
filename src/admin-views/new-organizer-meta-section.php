<?php
/**
 * Organizer metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$organizer_pto = get_post_type_object( Tribe__Events__Main::ORGANIZER_POST_TYPE );

?>
<script type="text/template" id="tmpl-tribe-create-organizer">
<tbody class="new-organizer">
	<tr class="organizer">
		<td><?php printf( esc_html__( '%s Name:', 'the-events-calendar' ), tribe_get_organizer_label_singular() ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Organizer][]' class='organizer-name' size='25' value='' />
		</td>
	</tr>
	<tr class="organizer">
		<td><?php esc_html_e( 'Phone:', 'the-events-calendar' ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Phone][]' class='organizer-phone' size='25' value='' />
		</td>
	</tr>
	<tr class="organizer">
		<td><?php esc_html_e( 'Website:', 'the-events-calendar' ); ?></td>
		<td>
			<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' name='organizer[Website][]' class='organizer-website' size='25' value='' />
		</td>
	</tr>
	<tr class="organizer">
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
</tbody>
</script>

<script type="text/javascript">
	(function($) {
		$('#event_organizer').on('blur', '.organizer-name', function () {
			var input = $(this);
			var group = input.parents('tbody');
			$.post(ajaxurl,
				{
					action: 'tribe_event_validation',
					nonce: '<?php echo wp_create_nonce( 'tribe-validation-nonce' ); ?>',
					type: 'organizer',
					name: input.val()
				},
				function (result) {
					if (result == 1) {
						group.find('.tribe-organizer-error').remove();
					} else {
						group.find('.tribe-organizer-error').remove();
						input.after('<div class="tribe-organizer-error error form-invalid"><?php printf( esc_html__( '%s Name Already Exists', 'the-events-calendar' ), tribe_get_organizer_label_singular() ); ?></div>');
					}
				}
			);
		})
	})(jQuery);
</script>
