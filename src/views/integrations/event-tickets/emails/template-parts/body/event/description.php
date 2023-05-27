<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Description.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets/emails/template-parts/body/event/description.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/tickets-emails-tpl Help article for Tickets Emails template files.
 *
 * @version TBD
 *
 * @since   TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

if ( empty( $event ) ) {
	return;
}
if ( empty( $event->excerpt ) ) {
	return;
}

?>
<tr>
	<td style="padding-bottom:20px;" class="tec-tickets__email-table-content-event-description-container">
		<?php echo (string) $event->excerpt; ?>
	</td>
</tr>
