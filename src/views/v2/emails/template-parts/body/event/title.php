<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Title.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/emails/template-parts/body/event/title.php
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
 *
 * @see tribe_get_event() For the format of the event object.
 */

if ( empty( $event ) ) {
	return;
}
if ( empty( $event->title ) ) {
	return;
}
?>
<tr>
	<td class="tec-tickets__email-table-content-event-title-container">
		<h3 class="tec-tickets__email-table-content-event-title">
			<?php
			// phpcs:ignore
			echo $event->title;
			?>
		</h3>
	</td>
</tr>
