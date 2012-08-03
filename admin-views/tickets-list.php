<table class="eventtable ticket_list">
	<?php
	$provider = null;
	$count    = 0;
	foreach ( $tickets as $ticket ) {
		$controls = array();

		$controls[] = sprintf( "<span><a href='#' attr-provider='%s' attr-ticket-id='%s' id='ticket_edit_%s' class='ticket_edit'>Edit</a></span>", $ticket->provider_class, $ticket->ID, $ticket->ID );
		$controls[] = sprintf( "<span><a href='#' attr-provider='%s' attr-ticket-id='%s' id='ticket_delete_%s' class='ticket_delete'>Delete</a></span>", $ticket->provider_class, $ticket->ID, $ticket->ID );
		if ( $ticket->admin_link ) {
			$controls[] = sprintf( "<span><a href='%s'>Edit in %s</a></span>", esc_url( $ticket->admin_link ), self::$active_modules[$ticket->provider_class] );
		}
		if ( $ticket->frontend_link ) {
			$controls[] = sprintf( "<span><a href='%s'>View</a></span>", esc_url( $ticket->frontend_link ) );
		}


		if ( $ticket->provider_class !== $provider ) {
			?>
			<td colspan="4" class="titlewrap">
				<h3><?php echo esc_html( self::$active_modules[$ticket->provider_class] ); ?>
					<?php
					$provider      = $ticket->provider_class;
					$provider_obj  = call_user_func( array( $provider,
					                                        'get_instance' ) );
					$reports_links = $provider_obj->get_reports_link();
					if ( $reports_links ) {
						?>
						<small><a href="<?php echo esc_url( $reports_links );?>">Reports</a></small>
						<?php
					}
					?>
				</h3>
			</td>
			<?php } ?>
		<tr>
			<td width="40%">
				<p class="ticket_name"><?php echo esc_html( $ticket->name ); ?></p>

				<div class="ticket_controls">
					<?php echo join( " | ", $controls ); ?>
				</div>

			</td>
			<td width="40%" valign="top">
				<small><?php echo esc_html( $ticket->description ); ?></small>
			</td>
			<td>
				<?php
				if ( $ticket->stock ) {
					?>
					<i>Stock: <?php echo esc_html( $ticket->stock ); ?></i>
					<?php
				}
				?>
			</td>
			<td>
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
	} ?>
</table>