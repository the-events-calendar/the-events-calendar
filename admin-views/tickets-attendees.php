<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h2><?php _e( "Attendees" );?></h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php _e( "Summary" );?></h3>
			<table class="eventtable ticket_list">
				<?php
				$event_id = isset( $_GET["event_id"] ) ? $_GET["event_id"] : 0;
				$tickets  = TribeEventsTickets::get_event_tickets( $event_id );

				$provider = null;
				$count    = 0;

				foreach ( $tickets as $ticket ) {

					$provider     = $ticket->provider_class;
					$provider_obj = call_user_func( array( $provider, 'get_instance' ) );

					if ( ( $ticket->provider_class !== $provider ) || $count == 0 ) {
						?>
						<td colspan="3">
							<h4><?php echo esc_html( self::$active_modules[$ticket->provider_class] ); ?></h4>
						</td>
						<?php } ?>
					<tr>
						<td width="40%">
							<p class="ticket_name"><?php
								echo $ticket->name;
								?></p>

						</td>
						<td valign="top">
							<?php echo  woocommerce_price( $ticket->price ); ?>
						</td>
						<td valign="top" nowrap="nowrap">
							<?php
							$stock = !empty ( $ticket->stock ) ? $ticket->stock : 0;
							$sold  = !empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;
							echo sprintf( __( "Sold %d of %d", 'tribe-events-calendar' ), $sold, $sold + $stock );
							?>
						</td>

					</tr>
					<?php
					$count++;
				} ?>
			</table>
		</div>
	</div>

	<form id="topics-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
		<input type="hidden" name="event_id" value="<?php echo $_GET['event_id'] ?>"/>
		<input type="hidden" name="post_type" value="<?php echo TribeEvents::POSTTYPE; ?>"/>
		<?php $attendees_table->display() ?>
	</form>
</div>