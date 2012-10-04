<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h2>Attendees</h2>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle">Summary</h3>
			<table class="eventtable ticket_list">
				<?php
				$event_id = isset( $_GET["event_id"] ) ? $_GET["event_id"] : 0;
				$tickets  = TribeEventsTickets::get_event_tickets( $event_id );

				$provider = null;
				$count    = 0;

				foreach ( $tickets as $ticket ) {

					$provider     = $ticket->provider_class;
					$provider_obj = call_user_func( array( $provider,
					                                       'get_instance' ) );

					if ( ( $ticket->provider_class !== $provider ) || $count == 0 ) {
						?>
						<td colspan="3" >
							<h4><?php echo esc_html( self::$active_modules[$ticket->provider_class] ); ?></h4>
						</td>
						<?php } ?>
					<tr>
						<td width="40%">
							<p class="ticket_name"><?php
								echo $ticket->name;
								?></p>

						</td>
						<td nowrap="nowrap">
							<?php
							if ( $ticket->stock ) {
								?>
								<i>Stock: <?php echo esc_html( $ticket->stock ); ?></i>
								<?php
							}
							?>
						</td>
						<td nowrap="nowrap">
							<?php
							if ( $ticket->qty_sold ) {
								?>
								<i>Sold: <?php echo esc_html( $ticket->qty_sold ); ?></i>
								<?php
							}
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