<table class="eventtable ticket_list eventForm">
	<?php
	$provider = null;
	$count    = 0;
	global $post;
	if ( $post ) {
		$post_id = get_the_ID();
	}else{
		$post_id = $_POST["post_ID"];
	}

	foreach ( $tickets as $ticket ) {

		$controls     = array();
		$provider     = $ticket->provider_class;
		$provider_obj = call_user_func( array( $provider,
		                                       'get_instance' ) );


		$controls[] = sprintf( "<span><a href='#' attr-provider='%s' attr-ticket-id='%s' id='ticket_edit_%s' class='ticket_edit'>Edit</a></span>", $ticket->provider_class, $ticket->ID, $ticket->ID );
		$controls[] = sprintf( "<span><a href='#' attr-provider='%s' attr-ticket-id='%s' id='ticket_delete_%s' class='ticket_delete'>Delete</a></span>", $ticket->provider_class, $ticket->ID, $ticket->ID );
		if ( $ticket->admin_link ) {
			$controls[] = sprintf( "<span><a href='%s'>Edit in %s</a></span>", esc_url( $ticket->admin_link ), self::$active_modules[$ticket->provider_class] );
		}
		if ( $ticket->frontend_link && get_post_status( get_the_ID() ) == 'publish' ) {
			$controls[] = sprintf( "<span><a href='%s'>View</a></span>", esc_url( $ticket->frontend_link ) );
		}

		$report = $provider_obj->get_ticket_reports_link( $post_id, $ticket->ID );
		if ( $report ) {
			$controls[] = $report;
		}

		if ( ( $ticket->provider_class !== $provider ) || $count == 0 ) {
			?>
			<td colspan="4" class="titlewrap">
				<h4 class="tribe_sectionheader"><?php echo esc_html( self::$active_modules[$ticket->provider_class] ); ?>
					<?php echo $provider_obj->get_event_reports_link( $post_id ); ?><small>&nbsp;|&nbsp;</small>
					<?php echo sprintf( "<small><a title='See who purchased tickets to this event' href='%s'>Attendees</a></small>", admin_url( sprintf( 'edit.php?post_type=%s&page=%s&event_id=%d', TribeEvents::POSTTYPE, $this->attendees_slug, $post_id ) ) ); ?>
				</h4>
			</td>
			<?php } ?>
		<tr>
			<td width="40%">
				<p class="ticket_name"><?php
					echo sprintf( "<a href='#' attr-provider='%s' attr-ticket-id='%s' class='ticket_edit'>%s</a></span>", $ticket->provider_class, $ticket->ID, esc_html( $ticket->name ) );
					?></p>

				<div class="ticket_controls">
					<?php echo join( " | ", $controls ); ?>
				</div>

			</td>
			<td width="40%" valign="top">
				<?php echo esc_html( $ticket->description ); ?>
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