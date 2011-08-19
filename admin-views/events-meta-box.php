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
<?php require_once('recurrence-dialog.php'); ?>
<div id="eventIntro">
<div id="tec-post-error" class="tec-events-error error"></div>
<?php $this->do_action('tribe_events_post_errors', $postId, true) ?>

</div>
<div id='eventDetails' class="inside eventForm bubble">
   <?php $this->do_action('tribe_events_detail_top', $postId, true) ?>
	<?php wp_nonce_field( TribeEvents::POSTTYPE, 'ecp_nonce' ); ?>
	<table cellspacing="0" cellpadding="0" id="EventInfo">
		<tr>
			<td colspan="2" class="tribe_sectionheader"><h4 class="event-time"><?php _e('Event Time &amp; Date', self::PLUGIN_DOMAIN); ?></h4></td>
		</tr>
		<tr id="recurrence-changed-row">
			<td colspan='2'><?php _e("You have changed the recurrence rules of this event.  Saving the event will update all future events.  If you did not mean to change all events, then please refresh the page.") ?></td>
		</tr>
		<tr>
			<td><?php _e('All day event?', self::PLUGIN_DOMAIN); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='checkbox' id='allDayCheckbox' name='EventAllDay' value='yes' <?php echo $isEventAllDay; ?> /></td>
		</tr>
		<tr>
			<td style="width:125px;"><?php _e('Start Date / Time:',self::PLUGIN_DOMAIN); ?></td>
			<td>
				<input autocomplete="off" tabindex="<?php $this->tabIndex(); ?>" type="text" class="datepicker" name="EventStartDate" id="EventStartDate"  value="<?php echo esc_attr($EventStartDate) ?>" />
				<span class="helper-text hide-if-js"><?php _e('YYYY-MM-DD', self::PLUGIN_DOMAIN) ?></span>
				<span class='timeofdayoptions'>
					<?php _e('@',self::PLUGIN_DOMAIN); ?>
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
			<td><?php _e('End Date / Time:',self::PLUGIN_DOMAIN); ?></td>
			<td>
				<input autocomplete="off" type="text" class="datepicker" name="EventEndDate" id="EventEndDate"  value="<?php echo esc_attr( $EventEndDate ); ?>" />
				<span class="helper-text hide-if-js"><?php _e('YYYY-MM-DD', self::PLUGIN_DOMAIN) ?></span>
				<span class='timeofdayoptions'>
					<?php _e('@',self::PLUGIN_DOMAIN); ?>
					<select class="spEventsInput" tabindex="<?php $this->tabIndex(); ?>" name='EventEndHour'>
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
	<div class="tribe_sectionheader" style="padding: 6px 6px 0 0; font-size: 11px; margin: 0 10px;"><h4><?php _e('Event Location Details', self::PLUGIN_DOMAIN); ?></h4></div>
	<div style="float: left;">
		<table id="event_venue" class="eventtable">
			<tr class="">
				<td style="width:170px"><?php _e('Use Saved Venue:',self::PLUGIN_DOMAIN); ?></td>
				<td>
					<?php $this->saved_venues_dropdown($_EventVenueID);?>
				</td>
			</tr>

			<?php
				include( $this->pluginPath . 'admin-views/venue-meta-box.php' );

			?>
			<tr id="google_map_link_toggle">
				<td><?php _e('Show Google Maps Link:',self::PLUGIN_DOMAIN); ?></td>
				<td>
					<?php // is the post new?
						$tecPostCustomKeys = get_post_custom_keys($postId);
						$tecHasCustomKeys = count( $tecPostCustomKeys );
						$tecNewPost = ( $tecHasCustomKeys ) ? !in_array( "_EventShowMapLink", $tecPostCustomKeys ) : true;
					?>
					<input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMapLink" name="EventShowMapLink" size="6" value="true" <?php if( $tecNewPost || get_post_meta( $postId, '_EventShowMapLink', true ) == 'true' ) echo 'checked="checked"'?> />
				</td>
			</tr>
			<?php if( tribe_get_option('embedGoogleMaps') ) : ?>
				<tr id="google_map_toggle">
					<td><?php _e('Show Google Map:',self::PLUGIN_DOMAIN); ?></td>
					<td><input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMap" name="EventShowMap" size="6" value="true" <?php if( $tecNewPost || get_post_meta( $postId, '_EventShowMap', true ) == 'true' ) echo 'checked="checked"'; ?> /></td>
				</tr>
			<?php endif; ?>
		</table>
	</div>
	<?php if( tribe_get_option('embedGoogleMaps') ): ?>
		<div style="float:right; display: <?php echo $tecNewPost || get_post_meta( $postId, '_EventShowMap', true) == 'true' ? "block" : "none" ?>">
			<?php echo tribe_get_embedded_map($postId, 200, 200, true) ?>
		</div>
	<?php endif; ?>
	<div style="clear:both"></div>
	<table id="event_organizer" class="eventtable">
			<tr>
				<td colspan="2" class="tribe_sectionheader"><h4><?php _e('Event Organizer Details', self::PLUGIN_DOMAIN); ?></h4></td>
			</tr>
			<tr class="" >
				<td style="width:170px"><?php _e('Use Saved Organizer:',self::PLUGIN_DOMAIN); ?></td>
				<td>
					<?php $this->saved_organizers_dropdown($_EventOrganizerID);?>
				</td>
			</tr>
				
			<?php
				include( $this->pluginPath . 'admin-views/organizer-meta-box.php' );

			?>
	</table>
    <?php $this->do_action('tribe_events_details_table_bottom', $postId, true) ?>
	<table id="event_cost" class="eventtable">		
		<?php if(!class_exists('Event_Tickets_PRO')){ ?>
		<tr>
			<td colspan="2" class="tribe_sectionheader"><h4><?php _e('Event Cost', self::PLUGIN_DOMAIN); ?></h4></td>
		</tr>
		<tr>
			<td><?php _e('Cost:',self::PLUGIN_DOMAIN); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventCost' name='EventCost' size='6' value='<?php echo esc_attr($_EventCost); ?>' /></td>
		</tr>
		<tr>
			<td></td>
			<td><small><?php _e('Leave blank to hide the field. Enter a 0 for events that are free.', self::PLUGIN_DOMAIN); ?></small></td>
		</tr>
		
		<?php } ?>
		<tr class="eventBritePluginPlug">
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php _e('Sell Tickets &amp; Track Registration', self::PLUGIN_DOMAIN); ?></h4>	
			</td>
		</tr>
		<?php if(!class_exists('Event_Tickets_PRO')){ ?>
			<tr class="eventBritePluginPlug">
				<td colspan="2">
					<p><?php printf( __('Interested in selling tickets and tracking registrations? We have an add-on in the works that will integrate your events and sell tickets on <a href="%s">EventBrite</a>. <a href="%s">Stay Tuned!</a>', self::PLUGIN_DOMAIN ), 'http://www.eventbrite.com/r/simpleevents', $this->envatoUrl ); ?></a></p>
				</td>
			</tr>
		<?php } ?>		
      <?php $this->do_action('tribe_events_cost_table', $postId, true) ?>
	</table>
	</div>
   <?php $this->do_action('tribe_events_above_donate', $postId, true) ?>
   <?php $this->do_action('tribe_events_details_bottom', $postId, true) ?>
