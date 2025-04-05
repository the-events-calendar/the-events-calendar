<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Location.
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
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event ) ) {
	return;
}

if ( empty( $event->venues ) ) {
	return;
}

$venue = $event->venues[0];

if ( empty( $venue ) ) {
	return;
}

?>
<tr>
	<td class="tec-tickets__email-table-content-event-venue-title-container">
		<h3 class="tec-tickets__email-table-content-event-venue-title">
			<?php echo esc_html_x( 'Event Location', 'Event location on the Ticket Email', 'the-events-calendar' ); ?>
		</h3>
	</td>
</tr>
<tr>
	<td class="tec-tickets__email-table-content-event-venue-container">
		<h2 class="tec-tickets__email-table-content-event-venue-name">
			<?php echo wp_kses_post( $venue->post_title ); ?>
		</h2>
		<table role="presentation" class="tec-tickets__email-table-content-event-venue-table">
			<tr>
				<td class="tec-tickets__email-table-content-event-venue-address-table-container">
					<?php $this->template( 'template-parts/body/event/venue/address', [ 'venue' => $venue ] ); ?>
				</td>
				<td class="tec-tickets__email-table-content-event-venue-phone-website-container">

					<?php $this->template( 'template-parts/body/event/venue/phone', [ 'venue' => $venue ] ); ?>

					<?php $this->template( 'template-parts/body/event/venue/website', [ 'venue' => $venue ] ); ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
