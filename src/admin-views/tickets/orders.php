<?php
$this->orders_table->prepare_items();

$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
$event = get_post( $event_id );
$tickets = Tribe__Events__Tickets__Tickets::get_event_tickets( $event_id );

ob_start();
$this->orders_table->display();
$table = ob_get_clean();
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-orders"><br></div>
	<h2><?php esc_html_e( 'Orders', 'tribe-events-calendar' ); ?></h2>

	<h2><?php echo $event->post_title; ?></h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php esc_html_e( 'Event Summary', 'tribe-events-calendar' ); ?></h3>


			<table class="eventtable ticket_list">
				<tr>
					<td width="66%" valign="top">
						<h4><?php esc_html_e( 'Ticket Sales', 'tribe-events-calendar' ); ?></h4>

						<?php

						$total_sold = 0;
						$total_pending = 0;
						$total_profit = 0;

						foreach ( $tickets as $ticket ) : ?>

							<strong><?php echo esc_html( $ticket->name ) ?>: </strong>
							<?php
							$stock = $ticket->stock;
							$sold = ! empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;

							$pending = '';

							if ( $ticket->qty_pending > 0 ) {
								$pending = sprintf( _n( '(%d awaiting review)', '(%d awaiting review)', 'tribe-events-calendar', $ticket->qty_pending ), (int) $ticket->qty_pending );
							}

							if ( empty( $stock ) && $stock !== 0 ) {
								echo sprintf( __( 'Sold %d %s', 'tribe-events-calendar' ), esc_html( $sold ), $pending );
							}
							else {
								echo sprintf( __( 'Sold %d of %d %s', 'tribe-events-calendar' ), esc_html( $sold ), esc_html( $sold + $stock ), $pending );
							}

							$price_format = get_woocommerce_price_format();

							echo sprintf( '(' . $price_format . ')', get_woocommerce_currency_symbol(), number_format( $tickets->price, 2 ) );

							echo '<br />';

							$total_sold += $sold;
							$total_pending += $ticket->qty_pending;
							$total_completed = $total_sold - $total_pending;

							$total_profit += $ticket->price;

						endforeach; ?>
					</td>
					<td width="33%" valign="middle">
						<div class="totals">

							<div id="total_tickets_sold_wrapper">
								<?php esc_html_e( 'Tickets sold:', 'tribe-events-calendar' ) ?>
								<span id="total_tickets_sold"><?php echo $total_sold ?></span>
							</div>

							<?php if ( $total_pending > 0 ) : ?>
								<div id="sales_breakdown_wrapper">
								<br />
									<?php esc_html_e( 'Finalized:', 'tribe-events-calendar' ); ?>
									<span id="total_issued"><?php echo $total_completed ?></span>

									<?php esc_html_e( 'Awaiting review:', 'tribe-events-calendar' ); ?>
									<span id="total_pending"><?php echo $total_pending ?></span>
								</div>
							<?php endif ?>

							<div id="total_profit_wrapper">
								<?php esc_html_e( 'Total profit:', 'tribe-events-calendar' ) ?>
								<span id="total_profit">
									<?php
									echo sprintf( get_woocommerce_price_format(), get_woocommerce_currency_symbol(), number_format( $total_profit, 2 ) );
									?>
								</span>
							</div>

						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<form id="topics-filter" method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_GET['page'] ) ? $_GET['page'] : '' ); ?>" />
		<input type="hidden" name="event_id" id="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr( Tribe__Events__Main::POSTTYPE ); ?>" />
		<?php
		echo $table;
		?>
	</form>
</div>
