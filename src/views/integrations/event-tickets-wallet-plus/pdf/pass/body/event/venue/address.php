<?php
/**
 * PDF Pass: Body - Venue Address
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/integrations/event-tickets-wallet-plus/pdf/pass/body/event/venue/address.php
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

if ( empty( $venue ) ) {
	return;
}

$comma_separator      = ', ';
$line_separator       = '<br />';
$append_after_address = array_map( 'trim', array_filter( [ $venue->state_province ?? null, $venue->state ?? null, $venue->province ?? null ] ) );

?>
<table class="tec-tickets__wallet-plus-pdf-event-venue-detail-table">
	<tr>
		<td>
			<table>
				<tr>
					<td width="24">
						<img
							width="9"
							height="12"
							src="<?php echo esc_url( $venue_map_pin_image_src ); ?>"
						/>
					</td>
					<td width="211">
						<div class="tec-tickets__wallet-plus-pdf-event-venue-detail-text">
							<?php
								echo esc_html( $venue->address );

								echo $line_separator;

								if ( ! empty( $venue->city ) ) :
									echo esc_html( $venue->city );
									if ( $append_after_address ) :
										echo $comma_separator;
									endif;
								endif;

								if ( $append_after_address ) :
									echo esc_html( reset( $append_after_address ) );
								endif;

								if ( ! empty( $venue->country ) ):
									echo $line_separator . esc_html( $venue->country );
								endif;

								if ( ! empty( $venue->directions_link ) ) :
									echo $line_separator;
									?>
									<a href="<?php echo esc_url( $venue->directions_link ); ?>"><?php
										echo esc_html_x( 'Get Directions', 'Link on the Ticket Email', 'the-events-calendar' );
									?></a>
								<?php
								endif;
							?>
						</div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>