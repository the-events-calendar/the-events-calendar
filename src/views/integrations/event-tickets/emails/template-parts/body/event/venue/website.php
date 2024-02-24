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
 * @version 6.1.1
 *
 * @since 6.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 * @var WP_Post $venue The venue post object.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $venue ) ) {
	return;
}

if ( empty( $venue->website_url ) ) {
	return;
}
?>
<table role="presentation" class="tec-tickets__email-table-content-event-venue-website-table">
	<tr>
		<td class="tec-tickets__email-table-content-event-venue-website-icon-container" valign="top" align="center">
			<img
				width="24"
				height="23"
				class="tec-tickets__email-table-content-event-venue-website-icon-image"
				src="<?php echo esc_url( tribe_resource_url( 'images/icons/bitmap/link.png', false, null, Tribe__Events__Main::instance() ) ); ?>"
			/>
		</td>
		<td class="tec-tickets__email-table-content-event-venue-website-container">
			<a
				href="<?php echo esc_url( $venue->website_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				style="overflow-wrap: anywhere;"
			>
				<?php echo esc_url( $venue->website_url ); ?>
			</a>
		</td>
	</tr>
</table>
