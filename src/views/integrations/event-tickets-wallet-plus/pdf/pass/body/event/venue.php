<?php
/**
 * PDF Pass: Body - Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets-wallet-plus/pdf/pass/body/event/venue.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/event-tickets-wallet-plus-tpl Help article for Wallet Plus template files.
 *
 * @since 6.2.8
 *
 * @version 6.2.8
 *
 * @var string $venue_map_pin_image_src The image source of the venue map pin icon.
 * @var string $venue_phone_image_src The image source of the venue phone icon.
 * @var string $venue_link_image_src The image source of the venue link icon.
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
