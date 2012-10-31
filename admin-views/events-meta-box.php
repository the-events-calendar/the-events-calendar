<?php
/**
* Events post main metabox
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<style type="text/css">
	<?php if( class_exists( 'Eventbrite_for_TribeEvents' ) ) : ?>
		.eventBritePluginPlug {display:none;}
	<?php endif; ?>
</style>
<div id="eventIntro">
<div id="tribe-events-post-error" class="tribe-events-error error"></div>
<?php $this->do_action('tribe_events_post_errors', $postId, true) ?>

</div>
<div id='eventDetails' class="inside eventForm bubble">
   <?php $this->do_action('tribe_events_detail_top', $postId, true) ?>
	<?php wp_nonce_field( TribeEvents::POSTTYPE, 'ecp_nonce' ); ?>
	<?php do_action('tribe_events_eventform_top', $postId); ?>
	<table cellspacing="0" cellpadding="0" id="EventInfo">
		<tr>
			<td colspan="2" class="tribe_sectionheader"><h4 class="event-time"><?php _e('Event Time &amp; Date', 'tribe-events-calendar'); ?></h4></td>
		</tr>
		<tr id="recurrence-changed-row">
			<td colspan='2'><?php _e("You have changed the recurrence rules of this event.  Saving the event will update all future events.  If you did not mean to change all events, then please refresh the page.", 'tribe-events-calendar') ?></td>
		</tr>
		<tr>
			<td><?php _e('All day event?', 'tribe-events-calendar'); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='checkbox' id='allDayCheckbox' name='EventAllDay' value='yes' <?php echo $isEventAllDay; ?> /></td>
		</tr>
		<tr>
			<td style="width:125px;"><?php _e('* Start Date / Time:','tribe-events-calendar'); ?></td>
			<td>
				<input autocomplete="off" tabindex="<?php $this->tabIndex(); ?>" type="text" class="datepicker" name="EventStartDate" id="EventStartDate"  value="<?php echo esc_attr($EventStartDate) ?>" />
				<span class="helper-text hide-if-js"><?php _e('YYYY-MM-DD', 'tribe-events-calendar') ?></span>
				<span class='timeofdayoptions'>
					<?php _e('@','tribe-events-calendar'); ?>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartHour'>
						<?php echo $startHourOptions; ?>
					</select>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartMinute'>
						<?php echo $startMinuteOptions; ?>
					</select>
					<?php if ( !strstr( get_option( 'time_format', TribeDateUtils::TIMEFORMAT ), 'H' ) ) : ?>
						<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartMeridian'>
							<?php echo $startMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td><?php _e('* End Date / Time:','tribe-events-calendar'); ?></td>
			<td>
				<input autocomplete="off" type="text" class="datepicker" name="EventEndDate" id="EventEndDate"  value="<?php echo esc_attr( $EventEndDate ); ?>" />
				<span class="helper-text hide-if-js"><?php _e('YYYY-MM-DD', 'tribe-events-calendar') ?></span>
				<span class='timeofdayoptions'>
					<?php _e('@','tribe-events-calendar'); ?>
					<select class="tribeEventsInput" tabindex="<?php $this->tabIndex(); ?>" name='EventEndHour'>
						<?php echo $endHourOptions; ?>
					</select>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMinute'>
						<?php echo $endMinuteOptions; ?>
					</select>
					<?php if ( !strstr( get_option( 'time_format', TribeDateUtils::TIMEFORMAT ), 'H' ) ) : ?>
						<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMeridian'>
							<?php echo $endMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
			</td>
		</tr>
		<?php $this->do_action('tribe_events_date_display', $postId, true) ?>
	</table>
	<div class="tribe_sectionheader" style="padding: 6px 6px 0 0; font-size: 11px; margin: 0 10px;"><h4><?php _e('Event Location Details', 'tribe-events-calendar'); ?></h4></div>
	<div style="float: left;">
		<table id="event_venue" class="eventtable">
         <?php do_action('tribe_venue_table_top', $postId) ?>
			<?php include( $this->pluginPath . 'admin-views/venue-meta-box.php' ); ?>
			<tr id="google_map_link_toggle">
				<td><?php _e('Show Google Maps Link:','tribe-events-calendar'); ?></td>
				<td>					
					<input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMapLink" name="EventShowMapLink" value="1" <?php checked((get_post_status($postId) == 'auto-draft') ? false : get_post_meta( $postId, '_EventShowMapLink', true )); ?> />
				</td>
			</tr>
			<?php if( tribe_get_option('embedGoogleMaps') ) : ?>
				<tr id="google_map_toggle">
					<td><?php _e('Show Google Map:','tribe-events-calendar'); ?></td>
					<td><input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMap" name="EventShowMap" value="1" <?php checked(tribe_embed_google_map($postId)); ?> /></td>
				</tr>
			<?php endif; ?>
		</table>
	</div>
   <?php do_action('tribe_after_location_details', $postId); ?>
	<table id="event_organizer" class="eventtable">
			<tr>
				<td colspan="2" class="tribe_sectionheader"><h4><?php _e('Event Organizer Details', 'tribe-events-calendar'); ?></h4></td>
			</tr>
         <?php do_action('tribe_organizer_table_top', $postId) ?>
			<?php include( $this->pluginPath . 'admin-views/organizer-meta-box.php' ); ?>
	</table>
    <?php $this->do_action('tribe_events_details_table_bottom', $postId, true) ?>
	<?php if ( !TribeEventsTickets::$active || class_exists( 'Event_Tickets_PRO' ) || ( get_post_meta( get_the_ID(), '_EventOrigin', true ) === 'community-events' ) ) { ?>
	<table id="event_cost" class="eventtable">
		<tr>
			<td colspan="2" class="tribe_sectionheader"><h4><?php _e('Event Cost', 'tribe-events-calendar'); ?></h4></td>
		</tr>
		<tr>
			<td><?php _e('Cost:','tribe-events-calendar'); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventCost' name='EventCost' size='6' value='<?php echo (isset($_EventCost)) ? esc_attr($_EventCost) : ''; ?>' /></td>
		</tr>
		<tr>
			<td></td>
			<td><small><?php _e('Leave blank to hide the field. Enter a 0 for events that are free.', 'tribe-events-calendar'); ?></small></td>
		</tr>
      <?php $this->do_action('tribe_events_cost_table', $postId, true) ?>
	</table>
	<?php } ?>
	</div>
   <?php $this->do_action('tribe_events_above_donate', $postId, true) ?>
   <?php $this->do_action('tribe_events_details_bottom', $postId, true) ?>
