<?php
/**
 * PDF Pass: Body - Venue Phone
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets-wallet-plus/pdf/pass/body/event/venue/phone.php
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

if ( empty( $venue->phone ) ) {
	return;
}

?>
<table class="tec-tickets__wallet-plus-pdf-event-venue-detail-table">
	<tr>
		<td>
			<table>
				<tr>
					<td width="24">
						<img
							width="12"
							height="11"
							src="<?php echo esc_url( $venue_phone_image_src ); ?>"
						/>
					</td>
					<td width="211">
						<div class="tec-tickets__wallet-plus-pdf-event-venue-detail-text">
							<?php echo esc_html( $venue->phone ); ?>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
