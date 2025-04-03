<?php
/**
 * Event Tickets Emails: Main template > Body > Event > Image.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets/emails/template-parts/body/event/image.php
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

if ( ! $event->thumbnail->exists ) {
	return;
}
?>
<tr>
	<td style="padding:0;" class="tec-tickets__email-table-content-event-image-container">
		<img
			class="tec-tickets__email-table-content-event-image"
			src="<?php echo esc_url( $event->thumbnail->full->url ); ?>"
			<?php if ( ! empty( $event->thumbnail->alt ) ) : ?>
				alt="<?php echo esc_attr( $event->thumbnail->alt ); ?>"
			<?php endif; ?>
			<?php if ( ! empty( $event->thumbnail->title ) ) : ?>
				title="<?php echo esc_attr( $event->thumbnail->title ); ?>"
			<?php endif; ?>
		/>
	</td>
</tr>
