<?php
$this->attendees_table->prepare_items();

$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
$event = get_post( $event_id );
$tickets  = TribeEventsTickets::get_event_tickets( $event_id );
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h2><?php _e( 'Attendees', 'tribe-events-calendar' ); ?></h2>

	<h2><?php echo $event->post_title; ?></h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php _e( 'Event Summary', 'tribe-events-calendar' ); ?></h3>


			<table class="eventtable ticket_list">
			<tr>
					<td width="33%" valign="top">
						<h4><?php _e( 'Event Details', 'tribe-events-calendar' ); ?></h4>

						<strong><?php _e( 'Start Date / Time:', 'tribe-events-calendar' ) ?></strong>
						<?php echo tribe_get_start_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>
						<br/>

						<strong><?php _e( 'End Date / Time:', 'tribe-events-calendar' ) ?></strong>
						<?php echo tribe_get_end_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>

						<?php
						// venue
						$venue_id = tribe_get_venue_id( $event_id );
						if ( ! empty( $venue_id ) )
							$venue = get_post( $venue_id );

						if ( ! empty( $venue ) ) : ?>
							<br/>
							<strong>
								<?php echo tribe_get_venue_label_singular() ?>
							</strong>
							<?php echo $venue->post_title; ?>

							<?php
							// phone
							$phone = get_post_meta( $venue_id, '_VenuePhone', true );

							if ( ! empty( $phone ) ) ?>
								<br/>
								<strong>
									<?php _e( 'Phone:', 'tribe-events-calendar' ) ?>
								</strong>
								<?php echo esc_html( $phone ); ?>
							<?php endif; ?>

							<?php
							// website
							$website = get_post_meta( $venue_id, '_VenueURL', true );
							if ( ! empty( $website ) ) : ?>
								<br/>
								<strong>
									<?php _e( 'Website:', 'tribe-events-calendar' ) ?>
									<a target="_blank" href="<?php echo esc_url( $website ) ?>"><?php echo esc_html( $website ) ?></a>
								</strong>
							<?php endif; ?>

						<?php endif; // if ( $venue ) ?>

					</td>
					<td width="33%" valign="top">
						<h4><?php _e( 'Ticket Sales', 'tribe-events-calendar' ); ?></h4>

						<?php

						$total_sold = 0;

						foreach ( $tickets as $ticket ) : ?>

						<strong><?php echo esc_html( $ticket->name ) ?>: </strong>
							<?php
							$stock = $ticket->stock;
							$sold  = ! empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;

							if ( empty( $stock ) && $stock !== 0 ) : ?>
								<?php echo sprintf( __( "Sold %d", 'tribe-events-calendar' ), esc_html( $sold ) ); ?>
							<?php else : ?>
								<?php echo sprintf( __( "Sold %d of %d", 'tribe-events-calendar' ), esc_html( $sold ), esc_html( $sold + $stock ) ); ?>
							<?php endif; ?>
							<br/>

							<?php $total_sold += $sold;

						endforeach; ?>
					</td>
					<td width="33%" valign="middle">
						<div class="totals">
							<?php

							$checkedin = TribeEventsTickets::get_event_checkedin_attendees_count( $event_id ); ?>

							<span id="total_tickets_sold_wrapper">
								<?php _e( 'Tickets sold:', 'tribe-events-calendar' ) ?> <span id="total_tickets_sold"><?php echo $total_sold ?></span>
							</span>
							<span id="total_checkedin_wrapper">
								<br/>
								<?php _e( 'Checked in:', 'tribe-events-calendar' ); ?> <span id="total_checkedin"><?php echo $checkedin ?></span>
							</span>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<form id="topics-filter" method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
		<input type="hidden" name="event_id" id="event_id" value="<?php echo $event_id; ?>" />
		<input type="hidden" name="post_type" value="<?php echo TribeEvents::POSTTYPE; ?>" />
		<?php $this->attendees_table->display()	?>
	</form>

	<div id="attendees_email_wrapper" title="<?php _e( 'Send the attendee list by email', 'tribe-events-calendar' );?>">
		<div id="email_errors"></div>
		<div id="email_send">
			<label for="email_to_user">
				<span><?php _e( 'Select a User:', 'tribe-events-calendar' );?></span>
				<?php wp_dropdown_users( array( 'name' => 'email_to_user', 'id' => 'email_to_user', 'show_option_none' => __( 'Select...', 'tribe-events-calendar' ), 'selected' => '' ) ); ?>
			</label>
			<span class="attendees_or"><?php _e( 'or', 'tribe-events-calendar' );?></span>
			<label for="email_to_address">
				<span><?php _e( 'Email Address:', 'tribe-events-calendar' );?></span>
				<input type="text" name="email_to_address" id="email_to_address" value="">
			</label>
		</div>
		<div id="email_response"></div>
	</div>

</div>