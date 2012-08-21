<?php
// Don't load directly
	if ( !defined( 'ABSPATH' ) ) {
		die( '-1' );
	}
?>

<table id="event_tickets" class="eventtable">
	<?php
	if ( get_post_meta( get_the_ID(), '_EventOrigin', true ) === 'community-events' ) {
		?>
		<tr>
			<td colspan="2" class="tribe_sectionheader updated">
				<p class="error-message"><?php _e( 'This event was created using Community Events. Are you sure you want to sell tickets for it?', 'tribe-events-calendar' ); ?></p>
			</td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td colspan="2" class="tribe_sectionheader ticket_list_container">

			<?php $this->ticket_list_markup( $tickets ); ?>

		</td>
	</tr>
	<tr>
		<td colspan="2" class="tribe_sectionheader">
			<a href="#" class="button-secondary" id="ticket_form_toggle">Add new ticket</a>
		</td>
	</tr>
	<tr id="ticket_form" class="ticket_form">
		<td colspan="2" class="tribe_sectionheader">
			<table id="ticket_form_table" class="eventtable ticket_form">
				<tr>
					<td width="40%"><label
						for="ticket_provider"><?php _e( 'Sell using:', 'tribe-events-calendar' ); ?></label></td>
					<td>
						<?php
						$checked = true;
						foreach ( self::$active_modules as $class => $module ) {
							?>
							<input <?php checked( $checked );?> type="radio" name="ticket_provider" id="ticket_provider"
							                                    value="<?php echo esc_attr( $class );?>"
							                                    class="ticket_field">
							<span><?php echo esc_html( $module ); ?></span>
							<?php
							$checked = false;
						}
						?>
					</td>
				</tr>
				<tr>
					<td><label for="ticket_name"><?php _e( 'Ticket Name:', 'tribe-events-calendar' ); ?></label></td>
					<td>
						<input type='text' id='ticket_name' name='ticket_name' class="ticket_field" size='25' value=''/>
					</td>
				</tr>
				<tr class="ticket">
					<td><label
						for="ticket_description"><?php _e( 'Ticket Description:', 'tribe-events-calendar' ); ?></label>
					</td>
					<td>
						<textarea rows="5" cols="40" name="ticket_description" class="ticket_field"
						          id="ticket_description"></textarea>
					</td>
				</tr>
				<tr class="ticket">
					<td><label
						for="ticket_price"><?php _e( 'Price:', 'tribe-events-calendar' ); ?></label>
					</td>
					<td>
						<input type='text' id='ticket_price' name='ticket_price' class="ticket_field" size='5'
						       value=''/>
						<small>(0 or empty for free tickets)</small>
					</td>
				</tr>


				<?php do_action( 'tribe_events_tickets_metabox_advanced', get_the_ID(), NULL ); ?>

				<tr class="ticket bottom">
					<td></td>
					<td>
						<input type="hidden" name="ticket_id" id="ticket_id" class="ticket_field" value=""/>

						<input type='button' id='ticket_form_save' name='ticket_form_save' value="Save this ticket"
						       class="button-primary"/>

						<input type='button' id='ticket_form_cancel' name='ticket_form_cancel' value="Cancel"
						       class="button-highlighted"/>
					</td>
				</tr>

			</table>

		</td>
	</tr>

</table>