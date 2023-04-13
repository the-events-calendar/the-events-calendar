<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Links.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/emails/template-parts/body/event/links.php
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

// @todo @juanfra: Add the links to the event files (if available).
?>
<tr>
	<td style="padding:0;">
		<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
			<tr>
				<td style="padding:30px 10px;text-align:center;width:100%" align="center">
					<a href="#" style="padding:0 8px;">
						<?php esc_html_e( 'Add event to iCal', 'event-tickets' ); ?>
					</a>
					<a href="#" style="padding:0 8px;">
						<?php esc_html_e( 'Add event to Google Calendar', 'event-tickets' ); ?>
					</a>
				</td>
			</tr>
		</table>
	</td>
</tr>
