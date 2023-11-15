<?php
/**
 * PDF Pass: Body - Venue Website
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets-wallet-plus/pdf/pass/body/event/venue/website.php
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

if ( empty( $venue->website ) ) {
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
							width="9"
							height="9"
							src="<?php echo esc_url( $venue_link_image_src ); ?>"
						/>
					</td>
					<td width="211">
						<div class="tec-tickets__wallet-plus-pdf-event-venue-detail-text">
							<a
								href="<?php echo esc_url( $venue->website ); ?>"
								rel="noopener noreferrer"
								class="tec-tickets__wallet-plus-pdf-event-venue-detail-link"
							>
								<?php echo esc_url( $venue->website ); ?></a>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
