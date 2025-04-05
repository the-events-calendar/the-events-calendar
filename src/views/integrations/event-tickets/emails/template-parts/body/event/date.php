<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Date.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets/emails/template-parts/body/event/date.php
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

$date = $event->schedule_details->value();

if ( empty( $date ) ) {
	return;
}
?>
<tr>
	<td class="tec-tickets__email-table-content-event-date-container">
		<p class="tec-tickets__email-table-content-event-date">
			<?php echo $date; // phpcs:ignore ?>
		</p>
	</td>
</tr>
