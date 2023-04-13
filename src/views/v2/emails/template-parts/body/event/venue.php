<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Location.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/emails/template-parts/body/event/venue.php
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

if ( empty( $event->venues ) ) {
	return;
}

if ( ! count( $event->venues ) ) {
	return;
}

$venue = $event->venues[0];

?>
<tr>
	<td style="padding:54px 0 12px 0">
		<h3 style="font-size:16px;font-weight:700;background:transparent;padding:0;margin:0;color:#141827">
			<?php esc_html_e( 'Event Location', 'the-events-calendar' ); ?>
		</h3>
	</td>
</tr>
<tr>
	<td style="border:1px solid #d5d5d5;padding:25px;">
		<h2 style="font-size: 18px;font-weight: 700;margin:0;padding:0;background:transparent;">
			<?php echo wp_kses_post( $venue->post_title ); ?>
		</h2>
		<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
			<tr>
				<td style="padding:12px 0 0 0; width: 50%;">
					<?php $this->template( 'template-parts/body/event/venue/address', [ 'venue' => $venue ] ); ?>
				</td>
				<td style="padding:0; width: 50%;">

					<?php $this->template( 'template-parts/body/event/venue/phone', [ 'venue' => $venue ] ); ?>

					<?php $this->template( 'template-parts/body/event/venue/website', [ 'venue' => $venue ] ); ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
