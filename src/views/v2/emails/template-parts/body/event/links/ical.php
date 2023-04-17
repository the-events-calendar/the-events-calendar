<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Links > iCalendar.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/emails/template-parts/body/event/links/ical.php
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
 * @var string  $event_ical_link The event Google Calendar link.
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event_ical_link ) ) {
	return;
}
?>
<a
	target="_blank"
	rel="noopener noreferrer"
	href="<?php echo esc_url( $event_ical_link ); ?>"
	style="padding:0 8px;"
>
	<?php esc_html_e( 'Add event to iCal', 'the-events-calendar' ); ?>
</a>
