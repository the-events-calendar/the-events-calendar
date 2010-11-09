<style type="text/css">
	<?php if( class_exists( 'Eventbrite_for_Events_Calendar_Pro' ) ) : ?>
		.eventBritePluginPlug {display:none;}
	<?php endif; ?>
</style>
<div id="eventIntro">
<div id="tec-post-error" class="tec-events-error error"></div>
<?php
try {
	do_action('sp_events_post_errors', $postId );
	if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
} catch ( TEC_Post_Exception $e) {
	$this->postExceptionThrown = true;
	update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
	$e->displayMessage( $postId );
}
?>

</div>
<div id='eventDetails' class="inside eventForm bubble">
	<?php
	try {
		do_action('sp_events_detail_top', $postId );
		if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
	} catch ( TEC_Post_Exception $e) {
		$this->postExceptionThrown = true;
		update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
		$e->displayMessage( $postId );
	}
	
	?>	<table cellspacing="0" cellpadding="0" id="EventInfo">
		<tr>
			<td colspan="2" class="snp_sectionheader"><h4 class="event-time"><?php _e('Event Time &amp; Date', $this->pluginDomain); ?></h4></td>
		</tr>
		<tr>
			<td><?php _e('All day event?', $this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='checkbox' id='allDayCheckbox' name='EventAllDay' value='yes' <?php echo $isEventAllDay; ?> /></td>
		</tr>
		<tr>
			<td style="width:125px;"><?php _e('Start Date / Time:',$this->pluginDomain); ?></td>
			<td>
				<input autocomplete="off" tabindex="<?php $this->tabIndex(); ?>" type="text" class="datepicker" name="EventStartDate" id="EventStartDate"  value="<?php echo $EventStartDate ?>" />
				<span class="helper-text hide-if-js"><?php _e('YYYY-MM-DD', $this->pluginDomain) ?></span>
				<span class='timeofdayoptions'>
					<?php _e('@',$this->pluginDomain); ?>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartHour'>
						<?php echo $startHourOptions; ?>
					</select>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartMinute'>
						<?php echo $startMinuteOptions; ?>
					</select>
					<?php if ( !strstr( get_option( 'time_format', Events_Calendar_Pro::TIMEFORMAT ), 'H' ) ) : ?>
						<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartMeridian'>
							<?php echo $startMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td><?php _e('End Date / Time:',$this->pluginDomain); ?></td>
			<td>
				<input autocomplete="off" type="text" class="datepicker" name="EventEndDate" id="EventEndDate"  value="<?php echo $EventEndDate; ?>" />
				<span class="helper-text hide-if-js"><?php _e('YYYY-MM-DD', $this->pluginDomain) ?></span>
				<span class='timeofdayoptions'>
					<?php _e('@',$this->pluginDomain); ?>
					<select class="spEventsInput" tabindex="<?php $this->tabIndex(); ?>" name='EventEndHour'>
						<?php echo $endHourOptions; ?>
					</select>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMinute'>
						<?php echo $endMinuteOptions; ?>
					</select>
					<?php if ( !strstr( get_option( 'time_format', Events_Calendar_Pro::TIMEFORMAT ), 'H' ) ) : ?>
						<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMeridian'>
							<?php echo $endMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
			</td>
		</tr>
	</table>
	<table id="event_venue" class="eventtable">
		<tr>
			<td colspan="2" class="snp_sectionheader"><h4><?php _e('Event Location Details', $this->pluginDomain); ?></h4></td>
		</tr>
		<tr class="">
			<td style="width:170px"><?php _e('Use Saved Venue:',$this->pluginDomain); ?></td>
			<td>
				<?php $this->saved_venues_dropdown($_EventVenueID);?>
			</td>
		</tr>
			
		<?php
			include( $this->pluginPath . 'views/venue-meta-box.php' );

		?>
		<tr id="google_map_link_toggle">
			<td><?php _e('Show Google Maps Link:',$this->pluginDomain); ?></td>
			<td>
				<?php // is the post new?
					$tecPostCustomKeys = get_post_custom_keys($postId);
					$tecHasCustomKeys = count( $tecPostCustomKeys );
					$tecNewPost = ( $tecHasCustomKeys ) ? !in_array( "_EventShowMapLink", $tecPostCustomKeys ) : true;
				?>
				<input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMapLink" name="EventShowMapLink" size="6" value="true" <?php if( $tecNewPost || get_post_meta( $postId, '_EventShowMapLink', true ) == 'true' ) echo 'checked="checked"'?> />
			</td>
		</tr>
		<?php if( sp_get_option('embedGoogleMaps') == 'on' ) : ?>
			<tr id="google_map_toggle">
				<td><?php _e('Show Google Map:',$this->pluginDomain); ?></td>
				<td><input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMap" name="EventShowMap" size="6" value="true" <?php if( $tecNewPost || get_post_meta( $postId, '_EventShowMap', true ) == 'true' ) echo 'checked="checked"'; ?> /></td>
			</tr>
		<?php endif; ?>
	</table>

	<table id="event_organizer" class="eventtable">
			<tr>
				<td colspan="2" class="snp_sectionheader"><h4><?php _e('Event Organizer Details', $this->pluginDomain); ?></h4></td>
			</tr>
			<tr class="" >
				<td style="width:170px"><?php _e('Use Saved Organizer:',$this->pluginDomain); ?></td>
				<td>
					<?php $this->saved_organizers_dropdown($_EventOrganizerID);?>
				</td>
			</tr>
				
			<?php
				include( $this->pluginPath . 'views/organizer-meta-box.php' );

			?>
	</table>

	<table id="event_cost" class="eventtable">
		<?php if(!class_exists('Event_Tickets_PRO')){ ?>
		<tr>
			<td colspan="2" class="snp_sectionheader"><h4><?php _e('Event Cost', $this->pluginDomain); ?></h4></td>
		</tr>
		<tr>
			<td><?php _e('Cost:',$this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventCost' name='EventCost' size='6' value='<?php echo $_EventCost; ?>' /></td>
		</tr>
		<tr>
			<td></td>
			<td><small><?php _e('Leave blank to hide the field. Enter a 0 for events that are free.', $this->pluginDomain); ?></small></td>
		</tr>
		
		<?php } ?>
		<tr class="eventBritePluginPlug">
			<td colspan="2" class="snp_sectionheader">
				<h4><?php _e('Sell Tickets &amp; Track Registration', $this->pluginDomain); ?></h4>	
			</td>
		</tr>
		<?php if(!class_exists('Event_Tickets_PRO')){ ?>
			<tr class="eventBritePluginPlug">
				<td colspan="2">
					<p><?php printf( __('Interested in selling tickets and tracking registrations? We have an add-on in the works that will integrate your events and sell tickets on <a href="%s">EventBrite</a>. <a href="%s">Stay Tuned!</a>', $this->pluginDomain ), 'http://www.eventbrite.com/r/simpleevents', $this->envatoUrl ); ?></a></p>
				</td>
			</tr>
		<?php } ?>
		<?php try {
		do_action( 'sp_events_cost_table', $postId );
		if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
			} catch ( TEC_Post_Exception $e) {
				$this->postExceptionThrown = true;
				update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
				$e->displayMessage( $postId );
			}?>
		
	</table>
	</div>
	<?php 
	try {
		do_action( 'sp_events_above_donate', $postId );
		if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
	} catch ( TEC_Post_Exception $e) {
		$this->postExceptionThrown = true;
		update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
		$e->displayMessage( $postId );
	}	
	?>

<?php
try {
	do_action( 'sp_events_details_bottom', $postId );
	if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
} catch ( TEC_Post_Exception $e) {
	$this->postExceptionThrown = true;
	update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
	$e->displayMessage( $postId );
}
?>