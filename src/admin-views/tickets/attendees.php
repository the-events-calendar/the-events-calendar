<?php
$this->attendees_table->prepare_items();

$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
$event = get_post( $event_id );
$tickets = Tribe__Events__Tickets__Tickets::get_event_tickets( $event_id );
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>

	<h1><?php esc_html_e( 'Attendees', 'tribe-events-calendar' ); ?></h1>
	<h1><?php echo apply_filters( 'tribe_events_tickets_attendees_event_title', $event->post_title, $event->ID ); ?></h1>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php esc_html_e( 'Event Summary', 'the-events-calendar' ); ?></h3>

			<?php do_action( 'tribe_events_tickets_attendees_event_summary_table_before', $event_id ); ?>

			<table class="eventtable ticket_list">
				<tr>
					<td width="33%" valign="top">
						<?php do_action( 'tribe_events_tickets_attendees_event_details_top', $event_id ); ?>

						<h4><?php esc_html_e( 'Event Details', 'the-events-calendar' ); ?></h4>

						<strong><?php esc_html_e( 'Start Date / Time:', 'the-events-calendar' ) ?></strong>
						<?php echo tribe_get_start_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>
						<br />

						<strong><?php esc_html_e( 'End Date / Time:', 'the-events-calendar' ) ?></strong>
						<?php
						echo tribe_get_end_date( $event_id, false, tribe_get_datetime_format( true ) );

						if ( tribe_has_venue( $event_id ) ) {
							$venue_id = tribe_get_venue_id( $event_id );
							?>

							<div class="venue-name">
								<strong><?php echo tribe_get_venue_label_singular(); ?>: </strong>
								<?php echo tribe_get_venue( $event_id ) ?>
							</div>

							<div class="venue-address">
								<strong><?php _e( 'Address:', 'the-events-calendar' ); ?> </strong>
								<?php echo tribe_get_full_address( $venue_id ); ?>
							</div>

							<?php
							if ( $phone = tribe_get_phone( $venue_id ) ) {
								?>
								<div class="venue-phone">
									<strong><?php echo esc_html( __( 'Phone:', 'the-events-calendar' ) ); ?> </strong>
									<?php echo esc_html( $phone ); ?>
								</div>
								<?php
							}//end if

							if ( $url = esc_url( get_post_meta( $venue_id, '_VenueURL', true ) ) ) {
								?>
								<div class="venue-url">
									<strong><?php echo esc_html( __( 'Website:', 'the-events-calendar' ) ); ?> </strong>
									<a target="_blank" href="<?php echo $url; ?>">
									<?php
									$display_url  = parse_url( $url, PHP_URL_HOST );
									$display_url .= parse_url( $url, PHP_URL_PATH ) ? '/&hellip;' : '';
									echo apply_filters( 'tribe_venue_display_url', $display_url, $url, $venue_id );
									?>
									</a>
								</div>
								<?php
							}//end if
						}//end if venue

						do_action( 'tribe_events_tickets_attendees_event_details_bottom', $event_id );
						?>
					</td>
					<td width="33%" valign="top">
						<?php do_action( 'tribe_events_tickets_attendees_ticket_sales_top', $event_id ); ?>

						<h4><?php esc_html_e( 'Ticket Sales', 'the-events-calendar' ); ?></h4>

						<?php

						$total_sold = 0;
						$total_pending = 0;

						foreach ( $tickets as $ticket ) {
							?>
							<strong><?php echo esc_html( $ticket->name ) ?>: </strong>
							<?php
							$stock = $ticket->stock;
							$sold = ! empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;

							$pending = '';

							if ( $ticket->qty_pending > 0 ) {
								$pending = sprintf( _n( '(%d awaiting review)', '(%d awaiting review)', 'the-events-calendar', $ticket->qty_pending ), (int) $ticket->qty_pending );
							}

							if ( empty( $stock ) && $stock !== 0 ) {
								echo sprintf( __( 'Sold %1$d %2$s', 'the-events-calendar' ), esc_html( $sold ), $pending );
							}
							else {
								echo sprintf( __( 'Sold %1$d of %2$d %3$s', 'the-events-calendar' ), esc_html( $sold ), esc_html( $sold + $stock ), $pending );
							}

							echo '<br />';

							$total_sold += $sold;
							$total_pending += $ticket->qty_pending;
							$total_completed = $total_sold - $total_pending;
						}//end foreach

						do_action( 'tribe_events_tickets_attendees_ticket_sales_bottom', $event_id );
						?>
					</td>
					<td width="33%" valign="middle">
						<div class="totals">
							<?php
							do_action( 'tribe_events_tickets_attendees_totals_top', $event_id );

							$checkedin = Tribe__Events__Tickets__Tickets::get_event_checkedin_attendees_count( $event_id ); ?>

							<span id="total_tickets_sold_wrapper">
								<?php esc_html_e( 'Tickets sold:', 'the-events-calendar' ) ?>
								<span id="total_tickets_sold"><?php echo $total_sold ?></span>
							</span>

							<?php
							if ( $total_pending > 0 ) {
								?>
								<span id="sales_breakdown_wrapper">
								<br />
									<?php esc_html_e( 'Finalized:', 'the-events-calendar' ); ?>
									<span id="total_issued"><?php echo $total_completed ?></span>

									<?php esc_html_e( 'Awaiting review:', 'the-events-calendar' ); ?>
									<span id="total_pending"><?php echo $total_pending ?></span>
								</span>
								<?php
							}//end if
							?>

							<span id="total_checkedin_wrapper">
								<br />
								<?php esc_html_e( 'Checked in:', 'the-events-calendar' ); ?>
								<span id="total_checkedin"><?php echo $checkedin ?></span>
							</span>

							<?php do_action( 'tribe_events_tickets_attendees_totals_bottom', $event_id ); ?>
						</div>
					</td>
				</tr>
			</table>

			<?php do_action( 'tribe_events_tickets_attendees_event_summary_table_after', $event_id ); ?>

		</div>
	</div>

	<form id="topics-filter" method="post">
		<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_GET['page'] ) ? $_GET['page'] : '' ); ?>" />
		<input type="hidden" name="event_id" id="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr( Tribe__Events__Main::POSTTYPE ); ?>" />
		<?php $this->attendees_table->display() ?>
	</form>

	<div id="attendees_email_wrapper" title="<?php esc_html_e( 'Send the attendee list by email', 'the-events-calendar' ); ?>">
		<div id="email_errors"></div>
		<div id="email_send">
			<label for="email_to_user">
				<span><?php esc_html_e( 'Select a User:', 'the-events-calendar' ); ?></span>
				<?php wp_dropdown_users(
					array(
						'name'             => 'email_to_user',
						'id'               => 'email_to_user',
						'show_option_none' => __( 'Select...', 'the-events-calendar' ),
						'selected'         => '',
					)
				); ?>
			</label>
			<span class="attendees_or"><?php esc_html_e( 'or', 'the-events-calendar' ); ?></span>
			<label for="email_to_address">
				<span><?php esc_html_e( 'Email Address:', 'the-events-calendar' ); ?></span>
				<input type="text" name="email_to_address" id="email_to_address" value="">
			</label>
		</div>
		<div id="email_response"></div>
	</div>
</div>
