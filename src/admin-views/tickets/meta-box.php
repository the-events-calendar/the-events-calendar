<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$header_id  = get_post_meta( get_the_ID(), $this->image_header_field, true );
$header_id  = ! empty( $header_id ) ? $header_id : '';
$header_img = '';
if ( ! empty( $header_id ) ) {
	$header_img = wp_get_attachment_image( $header_id, 'full' );
}

$modules = Tribe__Events__Tickets__Tickets::modules();
?>

<table id="event_tickets" class="eventtable">
	<?php
	if ( get_post_meta( get_the_ID(), '_EventOrigin', true ) === 'community-events' ) {
		?>
		<tr>
			<td colspan="2" class="tribe_sectionheader updated">
				<p class="error-message"><?php esc_html_e( 'This event was created using Community Events. Are you sure you want to sell tickets for it?', 'the-events-calendar' ); ?></p>
			</td>
		</tr>
	<?php
	}
	?>
	<tr>
		<td colspan="2" class="tribe_sectionheader updated">
			<table class="eventtable ticket_list eventForm">
				<tr class="tribe-tickets-image-upload">
					<td>
						<?php esc_html_e( 'Upload image for the ticket header', 'the-events-calendar' ); ?>
						<p class="description"><?php esc_html_e( 'The maximum image size in the email will be 580px wide by any height, and then scaled for mobile. If you would like "retina" support use an image sized to 1160px wide.', 'the-events-calendar' ); ?></p>
					</td>
					<td>
						<input type="button" class="button" name="tribe_ticket_header_image" id="tribe_ticket_header_image" value="<?php esc_html_e( 'Select an Image', 'the-events-calendar' ); ?>" />
					</td>
				</tr>
				<tr class="tribe-tickets-image-preview">
					<td colspan="2">
						<div class="tribe_preview" id="tribe_ticket_header_preview">
							<?php echo $header_img; ?>
						</div>
						<p class="description"><a href="#" id="tribe_ticket_header_remove"><?php esc_html_e( 'Remove' ); ?></a></p>

						<input type="hidden" id="tribe_ticket_header_image_id" name="tribe_ticket_header_image_id" value="<?php echo esc_attr( $header_id ); ?>" />
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="tribe_sectionheader ticket_list_container">

			<?php $this->ticket_list_markup( $tickets ); ?>

		</td>
	</tr>
	<tr>
		<td colspan="2" class="tribe_sectionheader">
			<a href="#" class="button-secondary"
			   id="ticket_form_toggle"><?php esc_html_e( 'Add new ticket', 'the-events-calendar' ); ?></a>
		</td>
	</tr>
	<tr id="ticket_form" class="ticket_form">
		<td colspan="2" class="tribe_sectionheader">
			<div id="tribe-loading"><span></span></div>
			<h4 class="ticket_form_title_add"><?php esc_html_e( 'Add new ticket', 'tribe-events-calendar' ); ?></h4>
			<h4 class="ticket_form_title_edit"><?php esc_html_e( 'Edit ticket', 'tribe-events-calendar' ); ?></h4>
			<table id="ticket_form_table" class="eventtable ticket_form">
				<tr>
					<td width="20%"><label for="ticket_provider"><?php esc_html_e( 'Sell using:', 'the-events-calendar' ); ?></label></td>
					<td>
						<?php
						$checked = true;
						foreach ( $modules as $class => $module ) {
							?>
							<input <?php checked( $checked ); ?> type="radio" name="ticket_provider" id="ticket_provider"
																 value="<?php echo esc_attr( $class ); ?>"
																 class="ticket_field">
							<span><?php echo esc_html( $module ); ?></span>
							<?php
							$checked = false;
						}
						?>
					</td>
				</tr>
				<tr>
					<td><label for="ticket_name"><?php esc_html_e( 'Ticket Name:', 'the-events-calendar' ); ?></label></td>
					<td>
						<input type='text' id='ticket_name' name='ticket_name' class="ticket_field" size='25' value='' />
					</td>
				</tr>
				<tr class="ticket">
					<td><label
							for="ticket_description"><?php esc_html_e( 'Ticket Description:', 'the-events-calendar' ); ?></label>
					</td>
					<td>
						<textarea rows="5" cols="40" name="ticket_description" class="ticket_field"
								  id="ticket_description"></textarea>
					</td>
				</tr>
				<tr class="ticket">
					<td><label
							for="ticket_price"><?php esc_html_e( 'Price:', 'the-events-calendar' ); ?></label>
					</td>
					<td>
						<input type='text' id='ticket_price' name='ticket_price' class="ticket_field" size='7'
							   value='' />

						<p class="description"><?php esc_html_e( '(0 or empty for free tickets)', 'the-events-calendar' ) ?></p>
						<p class="no-update-message" style="display: none;"></p>
					</td>
				</tr>

				<tr class="ticket sale_price">
					<td><label
							for="ticket_sale_price"><?php esc_html_e( 'Sale Price:', 'the-events-calendar' ) ?></label>
					</td>
					<td>
						<input type='text' id='ticket_sale_price' name='ticket_sale_price' class="ticket_field" size='7'
							value='' readonly />

						<p class="description"><?php esc_html_e( '(Current sale price - this can be managed via the product editor)', 'the-events-calendar' ) ?></p>
					</td>
				</tr>

				<tr class="ticket">
					<td><label
							for="ticket_start_date"><?php esc_html_e( 'Start sale:', 'the-events-calendar' ); ?></label>
					</td>
					<td>
						<input autocomplete="off" type="text" class="ticket_field" size='7' name="ticket_start_date"
							   id="ticket_start_date" value="">
						<span class="ticket_start_time ticket_time">
							<?php echo tribe_get_datetime_separator(); ?>
							<select name="ticket_start_hour" id="ticket_start_hour" class="ticket_field">
								<?php echo $startHourOptions; ?>
							</select>
							<select name="ticket_start_minute" id="ticket_start_minute" class="ticket_field">
								<?php echo $startMinuteOptions; ?>
							</select>
							<?php if ( ! strstr( get_option( 'time_format', Tribe__Events__Date_Utils::TIMEFORMAT ), 'H' ) ) : ?>
								<select name="ticket_start_meridian" id="ticket_start_meridian" class="ticket_field">
									<?php echo $startMeridianOptions; ?>
								</select>
							<?php endif; ?>
						</span>
					</td>
				</tr>

				<tr class="ticket">
					<td valign="top"><label
							for="ticket_price"><?php esc_html_e( 'End sale:', 'the-events-calendar' ); ?></label>
					</td>
					<td valign="top">
						<input autocomplete="off" type="text" class="ticket_field" size='7' name="ticket_end_date"
							   id="ticket_end_date" value="">

						<span class="ticket_end_time ticket_time">
							<?php echo tribe_get_datetime_separator(); ?>
							<select name="ticket_end_hour" id="ticket_end_hour" class="ticket_field">
								<?php echo $endHourOptions; ?>
							</select>
							<select name="ticket_end_minute" id="ticket_end_minute" class="ticket_field">
								<?php echo $endMinuteOptions; ?>
							</select>
							<?php if ( ! strstr( get_option( 'time_format', Tribe__Events__Date_Utils::TIMEFORMAT ), 'H' ) ) : ?>
								<select name="ticket_end_meridian" id="ticket_end_meridian" class="ticket_field">
									<?php echo $endMeridianOptions; ?>
								</select>
							<?php endif; ?>
						</span>
						<br />

						<p class="description"><?php esc_html_e( "When will ticket sales occur? If you don't set a start/end date for sales, tickets will be available from now until the event ends.", 'the-events-calendar' ); ?></p>
					</td>
				</tr>


				<?php do_action( 'tribe_events_tickets_metabox_advanced', get_the_ID(), null ); ?>

				<tr class="ticket bottom">
					<td></td>
					<td>
						<input type="hidden" name="ticket_id" id="ticket_id" class="ticket_field" value="" />
						<input type="button" id="ticket_form_save" name="ticket_form_save" value="<?php esc_html_e( 'Save this ticket', 'the-events-calendar' ); ?>" class="button-primary" />
						<input type="button" id="ticket_form_cancel" name="ticket_form_cancel" value="<?php esc_attr_e( 'Cancel', 'the-events-calendar' ); ?>" class="button-secondary" />
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
