<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Venue > Website.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets/emails/template-parts/body/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/tickets-emails-tpl Help article for Tickets Emails template files.
 *
 * @version TBD
 *
 * @since TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 * @var WP_Post $venue The venue post object.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $venue ) ) {
	return;
}

if ( empty( $venue->website ) ) {
	return;
}
?>
<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
	<tr>
		<td style="display:inline-block;text-align:center;vertical-align:top;" valign="top" align="center">
			<img
				width="24"
				height="23"
				style="width:24px;height:23px;display:block;"
				src="<?php echo esc_url( tribe_resource_url( 'icons/bitmap/link.png', false, null, Tribe__Events__Main::instance() ) ); ?>"
			/>
		</td>
		<td style="padding:0;">
			<a
				href="<?php echo esc_url( $venue->website ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				style="overflow-wrap: anywhere;"
			>
				<?php echo esc_url( $venue->website ); ?>
			</a>
		</td>
	</tr>
</table>
