<?php
/**
 * PDF Pass: Body - Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets-wallet-plus/pdf/pass/body/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since TBD
 *
 * @version TBD
 */

if ( empty( $venues ) ) {
	return;
}

$venue = reset( $venues );

?>
<table class="tec-tickets__wallet-plus-pdf-event-venue-table">
	<tr>
		<td>
			<?php $this->template( 'pdf/pass/body/event/venue/title', [ 'venue' => $venue ] ); ?>
			<?php $this->template( 'pdf/pass/body/event/venue/address', [ 'venue' => $venue ] ); ?>
			<?php $this->template( 'pdf/pass/body/event/venue/phone', [ 'venue' => $venue ] ); ?>
			<?php $this->template( 'pdf/pass/body/event/venue/website', [ 'venue' => $venue ] ); ?>
		</td>
	</tr>
</table>