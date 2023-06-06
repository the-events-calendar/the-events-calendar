<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Links > Google Calendar.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets/emails/template-parts/body/event/links/gcal.php
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
 * @var string  $event_gcal_link The event Google Calendar link.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event_gcal_link ) ) {
	return;
}
?>

<a
	target="_blank"
	rel="noopener noreferrer"
	href="<?php echo esc_url( $event_gcal_link ); ?>"
	class="tec-tickets__email-table-content-event-links-gcal-link"
>
	<?php echo esc_html_x( 'Add event to Google Calendar', 'Button on Ticket Email', 'the-events-calendar' ); ?>
</a>
