<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Links.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets/emails/template-parts/body/event/links.php
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
 * @var string  $event_ical_link The event iCal link.
 * @var string  $event_gcal_link The event Google Calendar link.
 *
 * @see tribe_get_event() For the format of the event object.
 */

?>
<tr>
	<td class="tec-tickets__email-table-content-event-links-container">
		<table role="presentation" class="tec-tickets__email-table-content-event-links-table">
			<tr>
				<td class="tec-tickets__email-table-content-event-links-table-data" align="center">

					<?php $this->template( 'template-parts/body/event/links/ical' ); ?>

					<?php $this->template( 'template-parts/body/event/links/gcal' ); ?>

				</td>
			</tr>
		</table>
	</td>
</tr>
