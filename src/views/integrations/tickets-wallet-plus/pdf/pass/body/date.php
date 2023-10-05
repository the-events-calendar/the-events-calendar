<?php
/**
 * PDF Pass: Body - Event Time
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/tickets-wallet-plus/pdf/pass/body/event-time.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since TBD
 *
 * @version TBD
 */

 if ( empty( $event ) ) {
	return;
}

$date = $event->schedule_details->value();

if ( empty( $date ) ) {
	return;
}

?>
<table class="tec-tickets__wallet-plus-pdf-event-date-table">
	<tr>
		<td>
			<?php echo $date; // phpcs:ignore ?>
		</td>
	</tr>
</table>