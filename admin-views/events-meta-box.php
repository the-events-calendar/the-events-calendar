<?php
/**
 * Events post main metabox
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<style type="text/css">
	<?php if( class_exists( 'Eventbrite_for_TribeEvents' ) ) : ?>
	.eventBritePluginPlug {
		display: none;
	}

	<?php endif; ?>
</style>
<div id="eventIntro">
	<div id="tribe-events-post-error" class="tribe-events-error error"></div>
	<?php $this->do_action( 'tribe_events_post_errors', $postId, true ) ?>

</div>
<div id='eventDetails' class="inside eventForm">
	<?php $this->do_action( 'tribe_events_detail_top', $postId, true ) ?>
	<?php wp_nonce_field( TribeEvents::POSTTYPE, 'ecp_nonce' ); ?>
	<?php do_action( 'tribe_events_eventform_top', $postId ); ?>
	<table cellspacing="0" cellpadding="0" id="EventInfo">
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<div class="tribe_sectionheader" style="">
					<h4><?php _e( 'Event Time &amp; Date', 'tribe-events-calendar' ); ?></h4></div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="eventtable">
					<tr id="recurrence-changed-row">
						<td colspan='2'><?php _e( "You have changed the recurrence rules of this event.  Saving the event will update all future events.  If you did not mean to change all events, then please refresh the page.", 'tribe-events-calendar' ) ?></td>
					</tr>
					<tr>
						<td><?php _e( 'All Day Event:', 'tribe-events-calendar' ); ?></td>
						<td>
							<input tabindex="<?php tribe_events_tab_index(); ?>" type="checkbox" id="allDayCheckbox" name="EventAllDay" value="yes" <?php echo $isEventAllDay; ?> />
						</td>
					</tr>
					<tr>
						<td style="width:175px;"><?php _e( 'Start Date &amp; Time:', 'tribe-events-calendar' ); ?></td>
						<td id="tribe-event-datepickers" data-startofweek="<?php echo get_option( 'start_of_week' ); ?>">
							<input autocomplete="off" tabindex="<?php tribe_events_tab_index(); ?>" type="text" class="tribe-datepicker" name="EventStartDate" id="EventStartDate" value="<?php echo esc_attr( $EventStartDate ) ?>" />
							<span class="helper-text hide-if-js"><?php _e( 'YYYY-MM-DD', 'tribe-events-calendar' ) ?></span>
				<span class="timeofdayoptions">
					@
					<select tabindex="<?php tribe_events_tab_index(); ?>" name="EventStartHour">
						<?php echo $startHourOptions; ?>
					</select>
					<select tabindex="<?php tribe_events_tab_index(); ?>" name="EventStartMinute">
						<?php echo $startMinuteOptions; ?>
					</select>
					<?php if ( ! TribeEventsViewHelpers::is_24hr_format() ) : ?>
						<select tabindex="<?php tribe_events_tab_index(); ?>" name="EventStartMeridian">
							<?php echo $startMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'End Date &amp; Time:', 'tribe-events-calendar' ); ?></td>
						<td>
							<input autocomplete="off" type="text" class="tribe-datepicker" name="EventEndDate" id="EventEndDate" value="<?php echo esc_attr( $EventEndDate ); ?>" />
							<span class="helper-text hide-if-js"><?php _e( 'YYYY-MM-DD', 'tribe-events-calendar' ) ?></span>
				<span class="timeofdayoptions">
					@
					<select class="tribeEventsInput" tabindex="<?php tribe_events_tab_index(); ?>" name="EventEndHour">
						<?php echo $endHourOptions; ?>
					</select>
					<select tabindex="<?php tribe_events_tab_index(); ?>" name="EventEndMinute">
						<?php echo $endMinuteOptions; ?>
					</select>
					<?php if ( ! TribeEventsViewHelpers::is_24hr_format() ) : ?>
						<select tabindex="<?php tribe_events_tab_index(); ?>" name="EventEndMeridian">
							<?php echo $endMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
						</td>
					</tr>
					<?php $this->do_action( 'tribe_events_date_display', $postId, true ) ?>
				</table>
			</td>
		</tr>
	</table>
	<table id="event_venue" class="eventtable">
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php _e( 'Event Location Details', 'tribe-events-calendar' ); ?></h4></td>
		</tr>
		<?php do_action( 'tribe_venue_table_top', $postId ) ?>
		<?php include( $this->pluginPath . 'admin-views/venue-meta-box.php' ); ?>
	</table>
	<?php do_action( 'tribe_after_location_details', $postId ); ?>
	<table id="event_organizer" class="eventtable">
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php _e( 'Event Organizer Details', 'tribe-events-calendar' ); ?></h4></td>
		</tr>
		<?php do_action( 'tribe_organizer_table_top', $postId ) ?>
		<?php include( $this->pluginPath . 'admin-views/organizer-meta-box.php' ); ?>
	</table>

	<table id="event_url" class="eventtable">
		<tr>
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php _e( 'Event Website', 'tribe-events-calendar' ); ?></h4></td>
		</tr>
		<tr>
			<td style="width:172px;"><?php _e( 'URL:', 'tribe-events-calendar' ); ?></td>
			<td>
				<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventURL' name='EventURL' size='25' value='<?php echo ( isset( $_EventURL ) ) ? esc_attr( $_EventURL ) : ''; ?>' placeholder='example.com' />
			</td>
		</tr>
		<?php $this->do_action( 'tribe_events_url_table', $postId, true ) ?>
	</table>

	<?php $this->do_action( 'tribe_events_details_table_bottom', $postId, true ) ?>

	<table id="event_cost" class="eventtable">
		<?php if ( tribe_events_admin_show_cost_field() ) : ?>
			<tr>
				<td colspan="2" class="tribe_sectionheader">
					<h4><?php _e( 'Event Cost', 'tribe-events-calendar' ); ?></h4></td>
			</tr>
			<tr>
				<td><?php _e( 'Currency Symbol:', 'tribe-events-calendar' ); ?></td>
				<td>
					<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventCurrencySymbol' name='EventCurrencySymbol' size='2' value='<?php echo ( isset( $_EventCurrencySymbol ) ) ? esc_attr( $_EventCurrencySymbol ) : tribe_get_option( 'defaultCurrencySymbol', '$' ); ?>' />
					<select tabindex="<?php tribe_events_tab_index(); ?>" id="EventCurrencyPosition" name="EventCurrencyPosition">
						<?php
						if ( isset( $_EventCurrencyPosition ) && 'suffix' === $_EventCurrencyPosition ) {
							$suffix = true;
						} elseif ( isset( $_EventCurrencyPosition ) && 'prefix' === $_EventCurrencyPosition ) {
							$suffix = false;
						} elseif ( true === tribe_get_option( 'reverseCurrencyPosition', false ) ) {
							$suffix = true;
						} else {
							$suffix = false;
						}
						?>
						<option value="prefix"> <?php _ex( 'Before cost', 'Currency symbol position', 'tribe-events-calendar' ) ?> </option>
						<option value="suffix"<?php if ( $suffix ) {
							echo ' selected="selected"';
						} ?>><?php _ex( 'After cost', 'Currency symbol position', 'tribe-events-calendar' ) ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php _e( 'Cost:', 'tribe-events-calendar' ); ?></td>
				<td>
					<input tabindex="<?php tribe_events_tab_index(); ?>" type='text' id='EventCost' name='EventCost' size='6' value='<?php echo ( isset( $_EventCost ) ) ? esc_attr( $_EventCost ) : ''; ?>' />
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<small><?php _e( 'Enter a 0 for events that are free or leave blank to hide the field.', 'tribe-events-calendar' ); ?></small>
				</td>
			</tr>
		<?php endif; ?>
		<?php $this->do_action( 'tribe_events_cost_table', $postId, true ) ?>
	</table>

</div>
<?php $this->do_action( 'tribe_events_above_donate', $postId, true ) ?>
<?php $this->do_action( 'tribe_events_details_bottom', $postId, true ) ?>
