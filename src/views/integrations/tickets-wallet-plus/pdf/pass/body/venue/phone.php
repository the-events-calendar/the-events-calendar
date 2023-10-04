<?php
/**
 * PDF Pass: Body - Venue Phone
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/tickets-wallet-plus/pdf/pass/body/venue/phone.php
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1amp
 *
 * @since TBD
 *
 * @version TBD
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
							src="<?php echo esc_url( tribe_resource_url( 'images/icons/bitmap/phone.png', false, null, Tribe__Events__Main::instance() ) ); ?>"
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