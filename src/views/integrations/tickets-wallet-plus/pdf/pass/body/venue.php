<?php
/**
 * PDF Pass: Body - Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/tickets-wallet-plus/pdf/pass/body/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since TBD
 *
 * @version TBD
 */

?>
<table class="tec-tickets__wallet-plus-pdf-event-venue-table">
	<tr>
		<td>
			<?php $this->template( 'pdf/pass/body/venue/title' ); ?>
			<?php $this->template( 'pdf/pass/body/venue/location' ); ?>
			<?php $this->template( 'pdf/pass/body/venue/phone' ); ?>
			<?php $this->template( 'pdf/pass/body/venue/website' ); ?>
		</td>
	</tr>
</table>