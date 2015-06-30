<?php
$this->attendees_table->prepare_items();

$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
$event = get_post( $event_id );
$tickets = Tribe__Events__Tickets__Tickets::get_event_tickets( $event_id );
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h2><?php esc_html_e( 'Attendees', 'tribe-events-calendar' ); ?></h2>

	<h2><?php echo $event->post_title; ?></h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php esc_html_e( 'Event Summary', 'tribe-events-calendar' ); ?></h3>


			<table class="eventtable ticket_list">
				<tr>
					<td width="33%" valign="top">
						<h4><?php esc_html_e( 'Event Details', 'tribe-events-calendar' ); ?></h4>

						<strong><?php esc_html_e( 'Start Date / Time:', 'tribe-events-calendar' ) ?></strong>
						<?php echo tribe_get_start_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>
						<br />

						<strong><?php esc_html_e( 'End Date / Time:', 'tribe-events-calendar' ) ?></strong>
						<?php echo tribe_get_end_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>

						<?php
						// venue
						$venue_id = tribe_get_venue_id( $event_id );
						if ( ! empty( $venue_id ) ) {
							$venue = get_post( $venue_id );
						}

						if ( ! empty( $venue ) ) : ?>
							<br />
							<strong>
								<?php echo tribe_get_venue_label_singular() ?>
							</strong>
							<?php echo $venue->post_title; ?>

							<?php
							// phone
							$phone = get_post_meta( $venue_id, '_VenuePhone', true );

							if ( ! empty( $phone ) ) : ?>
								<br />
								<strong><?php esc_html_e( 'Phone:', 'tribe-events-calendar' ); ?></strong>
								<?php echo esc_html( $phone );
							endif; ?>

							<?php
							// website
							$website = get_post_meta( $venue_id, '_VenueURL', true );
							if ( ! empty( $website ) ) : ?>
								<br />
								<strong><?php esc_html_e( 'Website:', 'tribe-events-calendar' ) ?></strong>
								<a target="_blank" href="<?php echo esc_url( $website ) ?>"><?php echo esc_html( $website ) ?></a>
							<?php endif; ?>

						<?php endif; // if ( $venue ) ?>

					</td>
					<td width="33%" valign="top">
						<h4><?php esc_html_e( 'Ticket Sales', 'tribe-events-calendar' ); ?></h4>

						<?php

						$total_sold = 0;
						$total_pending = 0;

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

							echo '<br />';

							$total_sold += $sold;
							$total_pending += $ticket->qty_pending;
							$total_completed = $total_sold - $total_pending;

						endforeach; ?>
					</td>
					<td width="33%" valign="middle">
						<div class="totals">
							<?php

							$checkedin = Tribe__Events__Tickets__Tickets::get_event_checkedin_attendees_count( $event_id ); ?>

							<span id="total_tickets_sold_wrapper">
								<?php esc_html_e( 'Tickets sold:', 'tribe-events-calendar' ) ?>
								<span id="total_tickets_sold"><?php echo $total_sold ?></span>
							</span>

							<?php if ( $total_pending > 0 ) : ?>
								<span id="sales_breakdown_wrapper">
								<br />
									<?php esc_html_e( 'Finalized:', 'tribe-events-calendar' ); ?>
									<span id="total_issued"><?php echo $total_completed ?></span>

									<?php esc_html_e( 'Awaiting review:', 'tribe-events-calendar' ); ?>
									<span id="total_pending"><?php echo $total_pending ?></span>
								</span>
							<?php endif ?>

							<span id="total_checkedin_wrapper">
								<br />
								<?php esc_html_e( 'Checked in:', 'tribe-events-calendar' ); ?>
								<span id="total_checkedin"><?php echo $checkedin ?></span>
							</span>

						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<form id="topics-filter" method="post">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
		<input type="hidden" name="event_id" id="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr( Tribe__Events__Main::POSTTYPE ); ?>" />
		<?php $this->attendees_table->display() ?>
	</form>

	<div id="attendees_email_wrapper" title="<?php esc_html_e( 'Send the attendee list by email', 'tribe-events-calendar' ); ?>">
		<div id="email_errors"></div>
		<div id="email_send">
			<label for="email_to_user">
				<span><?php esc_html_e( 'Select a User:', 'tribe-events-calendar' ); ?></span>
				<?php wp_dropdown_users(
					array(
						'name'             => 'email_to_user',
						'id'               => 'email_to_user',
						'show_option_none' => __( 'Select...', 'tribe-events-calendar' ),
						'selected'         => '',
					)
				); ?>
			</label>
			<span class="attendees_or"><?php esc_html_e( 'or', 'tribe-events-calendar' ); ?></span>
			<label for="email_to_address">
				<span><?php esc_html_e( 'Email Address:', 'tribe-events-calendar' ); ?></span>
				<input type="text" name="email_to_address" id="email_to_address" value="">
			</label>
		</div>
		<div id="email_response"></div>
	</div>
</div>
