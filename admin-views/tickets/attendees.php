<?php
$event_id = isset( $_GET["event_id"] ) ? intval( $_GET["event_id"] ) : 0;
$event    = get_post( $event_id );
$tickets  = TribeEventsTickets::get_event_tickets( $event_id );
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h2><?php _e( 'Attendees', 'tribe-events-calendar' );?></h2>
	<h2><?php echo $event->post_title; ?></h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php _e( 'Event Summary', 'tribe-events-calendar' );?></h3>



			<table class="eventtable ticket_list">
				<tr>
					<td width="33%" valign="top">
						<?php
						echo sprintf( '<h4>%s</h4>', esc_html( __( 'Event Details', 'tribe-events-calendar' ) ) );

						echo sprintf( '<strong>%s </strong> %s', esc_html( __( 'Start Date / Time:', 'tribe-events-calendar' ) ), tribe_get_start_date( $event_id, false, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) );
						echo "<br/>";
						echo sprintf( '<strong>%s </strong> %s', esc_html( __( 'End Date / Time:', 'tribe-events-calendar' ) ), tribe_get_end_date( $event_id, false, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) );

						$venue_id = tribe_get_venue_id( $event_id );
						if ( ! empty( $venue_id ) )
							$venue = get_post( $venue_id );

						if ( ! empty( $venue ) ) {
							echo "<br/>";
							echo sprintf( '<strong>%s </strong> %s', esc_html( __( 'Venue:', 'tribe-events-calendar' ) ), esc_html( $venue->post_title ) );

							$phone = get_post_meta( $venue_id, '_VenuePhone', true );
							if ( ! empty( $phone ) ) {
								echo "<br/>";
								echo sprintf( '<strong>%s </strong> %s', esc_html( __( 'Phone:', 'tribe-events-calendar' ) ), esc_html( $phone ) );
							}

							$website = get_post_meta( $venue_id, '_VenueURL', true );
							if ( ! empty( $website ) ) {
								echo "<br/>";
								echo sprintf( '<strong>%s </strong> <a target="_blank" href="%s">%s</a>', esc_html( __( 'Website:', 'tribe-events-calendar' ) ), esc_url( $website ), esc_html( $website ) );
							}
						}
						?>
					</td>
					<td width="33%" valign="top">
						<?php
						echo sprintf( '<h4>%s</h4>', esc_html( __( 'Ticket Sales', 'tribe-events-calendar' ) ) );

						$total_sold = 0;

						foreach ( $tickets as $ticket ) {

							echo sprintf( '<strong>%s: </strong>', esc_html( $ticket->name ) );

							$stock = $ticket->stock;
							$sold  = ! empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;

							if ( empty( $stock ) && $stock !== 0 ) {
								echo sprintf( __( "Sold %d", 'tribe-events-calendar' ), esc_html( $sold ) );
							} else {
								echo sprintf( __( "Sold %d of %d", 'tribe-events-calendar' ), esc_html( $sold ), esc_html( $sold + $stock ) );
							}

							$total_sold += $sold;

							echo "<br/>";
						}
						?>
					</td>
					<td width="33%" valign="middle">
						<div class="totals">
							<?php

							$checkedin = TribeEventsTickets::get_event_checkedin_attendees_count( $event_id );
							echo '<span id="total_tickets_sold_wrapper">';
							echo sprintf( '%s <span id="total_tickets_sold">%d</span>', esc_html( __( 'Tickets sold:', 'tribe-events-calendar' ) ), $total_sold );
							echo '</span>';
							echo '<span id="total_checkedin_wrapper">';
							echo "<br/>";
							echo sprintf( '%s <span id="total_checkedin">%d</span>', esc_html( __( 'Checked in:', 'tribe-events-calendar' ) ), $checkedin );
							echo '</span>';
							?>
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
		<?php
		$this->attendees_table->prepare_items();
		$this->attendees_table->display()
		?>
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