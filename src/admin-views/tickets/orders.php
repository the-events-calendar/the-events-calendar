<?php
$this->orders_table->prepare_items();

$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
$event = get_post( $event_id );
$tickets = Tribe__Events__Tickets__Tickets::get_event_tickets( $event_id );

/**
 * Filters whether or not fees are being passed to the end user (purchaser)
 *
 * @var boolean $pass_fees Whether or not to pass fees to user
 * @var int $event_id Event post ID
 */
Tribe__Events__Tickets__Orders_Table::$pass_fees_to_user = apply_filters( 'tribe_tickets_pass_fees_to_user', true, $event_id );

/**
 * Filters the fee percentage to apply to a ticket/order
 *
 * @var float $fee_percent Fee percentage
 */
Tribe__Events__Tickets__Orders_Table::$fee_percent = apply_filters( 'tribe_tickets_fee_percent', 0, $event_id );

/**
 * Filters the flat fee to apply to a ticket/order
 *
 * @var float $fee_flat Flat fee
 */
Tribe__Events__Tickets__Orders_Table::$fee_flat = apply_filters( 'tribe_tickets_fee_flat', 0, $event_id );

ob_start();
$this->orders_table->display();
$table = ob_get_clean();

$organizer = get_user_by( 'id', $event->post_author );

$event_revenue = Tribe__Events__Tickets__Orders_Table::event_revenue( $event_id );
$event_sales = Tribe__Events__Tickets__Orders_Table::event_sales( $event_id );
$event_fees = Tribe__Events__Tickets__Orders_Table::event_fees( $event_id );
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-orders"><br></div>
	<h2><?php esc_html_e( 'Orders', 'the-events-calendar' ); ?></h2>

	<h2><?php echo esc_html( get_the_title( $event->ID ) ); ?></h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<table class="eventtable ticket_list">
				<tr>
					<td width="33%" valign="top">
						<h4><?php esc_html_e( 'Event Summary', 'the-events-calendar' ); ?></h4>
						<div class="tribe-event-meta tribe-event-meta-date">
							<strong><?php echo esc_html__( 'Date:', 'the-events-calendar' ); ?></strong>
							<?php echo esc_html( tribe_get_start_date( $event, false ) ); ?>
						</div>
						<div class="tribe-event-meta tribe-event-meta-id">
							<strong><?php echo esc_html__( 'Event ID:', 'the-events-calendar' ); ?></strong>
							<?php echo absint( $event_id ); ?>
						</div>
						<div class="tribe-event-meta tribe-event-meta-organizer">
							<strong><?php echo esc_html__( 'Organizer:', 'the-events-calendar' ); ?></strong>
							<a href="<?php echo esc_url( add_query_arg( array( 'user_id' => $organizer->ID ), admin_url( 'profile.php' ) ) ); ?>"><?php echo esc_html( $organizer->user_nicename ); ?></a>
							<?php echo esc_html( sprintf( _x( ' (ID: %s)', 'ID of community organizer', 'the-events-calendar' ), absint( $event->post_author ) ) ); ?>
						</div>
						<?php do_action( 'tribe_events_community_orders_report_after_organizer', $event, $organizer ); ?>
					</td>
					<td width="33%" valign="top">
						<h4><?php esc_html_e( 'Ticket Sales', 'the-events-calendar' ); ?></h4>

						<?php

						$tickets_sold = array();
						$total_sold = 0;
						$total_pending = 0;
						$total_profit = 0;
						$total_completed = 0;

						foreach ( $tickets as $ticket ) {
							if ( empty( $tickets_sold[ $ticket->name ] ) ) {
								$tickets_sold[ $ticket->name ] = array(
									'ticket' => $ticket,
									'has_stock' => ! ( empty( $ticket->stock ) && 0 !== $ticket->stock ),
									'sku' => get_post_meta( $ticket->ID, '_sku', true ),
									'sold' => 0,
									'pending' => 0,
									'completed' => 0,
								);
							}
							$stock = $ticket->stock;
							$sold = ! empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;

							$tickets_sold[ $ticket->name ]['sold'] += $sold;
							$tickets_sold[ $ticket->name ]['pending'] += absint( $ticket->qty_pending );
							$tickets_sold[ $ticket->name ]['completed'] += absint( $tickets_sold[ $ticket->name ]['sold'] ) - absint( $tickets_sold[ $ticket->name ]['pending'] );

							$total_sold += $sold;
							$total_pending += absint( $ticket->qty_pending );
						}

						$total_completed += absint( $total_sold ) - absint( $total_pending );
						?>
						<div class="tribe-event-meta tribe-event-meta-tickets-sold">
							<strong><?php echo esc_html__( 'Tickets sold:', 'the-events-calendar' ); ?></strong>
							<?php echo absint( $total_sold ); ?>
							<?php if ( $total_pending > 0 ) : ?>
								<div id="sales_breakdown_wrapper" class="tribe-event-meta-note">
									<div>
										<?php esc_html_e( 'Completed:', 'the-events-calendar' ); ?>
										<span id="total_issued"><?php echo $total_completed ?></span>
									</div>
									<div>
										<?php esc_html_e( 'Awaiting review:', 'the-events-calendar' ); ?>
										<span id="total_pending"><?php echo $total_pending ?></span>
									</div>
								</div>
							<?php endif ?>
						</div>
						<?php
						foreach ( $tickets_sold as $ticket_sold ) {
							$price = '';
							$pending = '';
							$sold_message = '';

							if ( $ticket_sold['pending'] > 0 ) {
								$pending = sprintf( _n( '(%d awaiting review)', '(%d awaiting review)', 'the-events-calendar', $ticket_sold['pending'] ), (int) $ticket_sold['pending'] );
							}

							if ( ! $ticket_sold['has_stock'] ) {
								$sold_message = sprintf( __( 'Sold %d %s', 'the-events-calendar' ), esc_html( $ticket_sold['sold'] ), $pending );
							} else {
								$sold_message = sprintf( __( 'Sold %d of %d %s', 'the-events-calendar' ), esc_html( $ticket_sold['sold'] ), esc_html( $ticket_sold['sold'] + absint( $ticket_sold['ticket']->stock ) ), $pending );
							}

							if ( $ticket_sold['ticket']->price ) {
								$price_format = get_woocommerce_price_format();
								$price = sprintf( ' (' . $price_format . ')', get_woocommerce_currency_symbol(), number_format( $ticket_sold['ticket']->price, 2 ) );
							}
							?>
							<div class="tribe-event-meta tribe-event-meta-tickets-sold-itemized">
								<strong><?php echo esc_html( $ticket_sold['ticket']->name . $price ); ?>:</strong>
								<?php
								echo esc_html( $sold_message );
								if ( $ticket_sold['sku'] ) {
									?>
									<div class="tribe-event-meta-note tribe-event-ticket-sku">
										<?php printf( esc_html__( 'SKU: (%s)', 'the-events-calendar' ), esc_html( $ticket_sold['sku'] ) ); ?>
									</div>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</td>
					<td width="33%" valign="top">
						<h4>Totals</h4>

						<div class="tribe-event-meta tribe-event-meta-total-revenue">
							<strong><?php esc_html_e( 'Total Revenue:', 'the-events-calendar' ) ?></strong>
							<?php
							printf(
								get_woocommerce_price_format(),
								get_woocommerce_currency_symbol(),
								number_format( $event_revenue, 2 )
							);

							if ( $event_fees ) {
								?>
								<div class="tribe-event-meta-note">
									<?php echo esc_html__( '(Tickets + Site Fees)', 'the-events-calendar' ); ?>
								</div>
								<?php
							}
							?>
						</div>
						<?php
						if ( $event_fees ) {
							?>
							<div class="tribe-event-meta tribe-event-meta-total-ticket-sales">
								<strong><?php esc_html_e( 'Total Ticket Sales:', 'the-events-calendar' ) ?></strong>
								<?php
								printf(
									get_woocommerce_price_format(),
									get_woocommerce_currency_symbol(),
									number_format( $event_sales, 2 )
								);
								?>
							</div>
							<div class="tribe-event-meta tribe-event-meta-total-site-fees">
								<strong><?php esc_html_e( 'Total Site Fees:', 'the-events-calendar' ) ?></strong>
								<?php
								printf(
									get_woocommerce_price_format(),
									get_woocommerce_currency_symbol(),
									number_format( $event_fees, 2 )
								);
								?>
								<div class="tribe-event-meta-note">
									<?php
									echo apply_filters( 'tribe_events_orders_report_site_fees_note', '', $event, $organizer );
									?>
								</div>
							</div>
							<?php
						}//end if
						?>
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
