<style type="text/css">
	<?php if( class_exists( 'Eventbrite_for_The_Events_Calendar' ) ) : ?>
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
<div id='eventDetails' class="inside eventForm">
	<?php
	try {
		do_action('sp_events_detail_top', $postId );
		if( !$this->postExceptionThrown ) delete_post_meta( $postId, self::EVENTSERROROPT );
	} catch ( TEC_Post_Exception $e) {
		$this->postExceptionThrown = true;
		update_post_meta( $postId, self::EVENTSERROROPT, trim( $e->getMessage() ) );
		$e->displayMessage( $postId );
	}
	
	?>
	<table cellspacing="0" cellpadding="0" id="EventInfo">
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
					<?php if ( !strstr( get_option( 'time_format', The_Events_Calendar::TIMEFORMAT ), 'H' ) ) : ?>
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
					<select class="spEventsInput"tabindex="<?php $this->tabIndex(); ?>" name='EventEndHour'>
						<?php echo $endHourOptions; ?>
					</select>
					<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMinute'>
						<?php echo $endMinuteOptions; ?>
					</select>
					<?php if ( !strstr( get_option( 'time_format', The_Events_Calendar::TIMEFORMAT ), 'H' ) ) : ?>
						<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMeridian'>
							<?php echo $endMeridianOptions; ?>
						</select>
					<?php endif; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="snp_sectionheader"><h4><?php _e('Event Location Details', $this->pluginDomain); ?></h4></td>
		</tr>
		<tr>
			<td><?php _e('Venue:',$this->pluginDomain); ?></td>
			<td>
				<input tabindex="<?php $this->tabIndex(); ?>" type='text' name='EventVenue' size='25'  value='<?php echo $_EventVenue; ?>' />
			</td>
		</tr>
		<tr>
			<td><?php _e('Country:',$this->pluginDomain); ?></td>
			<td>
				<select tabindex="<?php $this->tabIndex(); ?>" name="EventCountry" id="EventCountry">
					<?php
					$this->constructCountries( $postId );
					$defaultCountry = sp_get_option('defaultCountry');
					if( $_EventCountry ) {
						foreach ($this->countries as $abbr => $fullname) {
							echo '<option label="' . $abbr . '" value="' . $fullname . '" ';
				       		if ($_EventCountry == $fullname) {
								echo 'selected="selected" ';
								$eventCountryLabel = $abbr;
							}
							echo '>' . $fullname . '</option>';
				     	}
					} elseif( $defaultCountry && !get_post_custom_keys( $postId ) ) {
						foreach ($this->countries as $abbr => $fullname) {
							echo '<option label="' . $abbr . '" value="' . $fullname . '" ';
				       		if ($defaultCountry[1] == $fullname) {
								echo 'selected="selected" ';
								$eventCountryLabel = $abbr;
							}
							echo '>' . $fullname . '</option>';
				     	}
					} else {
						$eventCountryLabel = "";
						foreach ($this->countries as $abbr => $fullname) {
							echo '<option label="' . $abbr . '" value="' . $fullname . '" >' . $fullname . '</option>';
				     	}
					}
				     ?>
			     </select>
				 <input name="EventCountryLabel" type="hidden" value="<?php echo $eventCountryLabel; ?>" />
			</td>
		</tr>
		<tr>
			<td><?php _e('Address:',$this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='EventAddress' size='25' value='<?php echo $_EventAddress; ?>' /></td>
		</tr>
		<tr>
			<td><?php _e('City:',$this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='EventCity' size='25' value='<?php echo $_EventCity; ?>' /></td>
		</tr>
		<input name="EventStateExists" type="hidden" value="<?php echo ($_EventCountry !== 'United States') ? 0 : 1; ?>">
		<tr id="International" <?php if($_EventCountry == 'United States' || $_EventCountry == '' ) echo('class="tec_hide"'); ?>>
			<td><?php _e('Province:',$this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' name='EventProvince' size='10' value='<?php echo $_EventProvince; ?>' /></td>
		</tr>
		<tr id="USA" <?php if($_EventCountry !== 'United States') echo('class="tec_hide"'); ?>>
			<td><?php _e('State:',$this->pluginDomain); ?></td>
			<td>
				<select tabindex="<?php $this->tabIndex(); ?>" name="EventState">
				    <option value=""><?php _e('Select a State:',$this->pluginDomain); ?></option> 
					<?php $states = array (
						"AL" => __("Alabama", $this->pluginDomain),
						"AK" => __("Alaska", $this->pluginDomain),
						"AZ" => __("Arizona", $this->pluginDomain),
						"AR" => __("Arkansas", $this->pluginDomain),
						"CA" => __("California", $this->pluginDomain),
						"CO" => __("Colorado", $this->pluginDomain),
						"CT" => __("Connecticut", $this->pluginDomain),
						"DE" => __("Delaware", $this->pluginDomain),
						"DC" => __("District of Columbia", $this->pluginDomain),
						"FL" => __("Florida", $this->pluginDomain),
						"GA" => __("Georgia", $this->pluginDomain),
						"HI" => __("Hawaii", $this->pluginDomain),
						"ID" => __("Idaho", $this->pluginDomain),
						"IL" => __("Illinois", $this->pluginDomain),
						"IN" => __("Indiana", $this->pluginDomain),
						"IA" => __("Iowa", $this->pluginDomain),
						"KS" => __("Kansas", $this->pluginDomain),
						"KY" => __("Kentucky", $this->pluginDomain),
						"LA" => __("Louisiana", $this->pluginDomain),
						"ME" => __("Maine", $this->pluginDomain),
						"MD" => __("Maryland", $this->pluginDomain),
						"MA" => __("Massachusetts", $this->pluginDomain),
						"MI" => __("Michigan", $this->pluginDomain),
						"MN" => __("Minnesota", $this->pluginDomain),
						"MS" => __("Mississippi", $this->pluginDomain),
						"MO" => __("Missouri", $this->pluginDomain),
						"MT" => __("Montana", $this->pluginDomain),
						"NE" => __("Nebraska", $this->pluginDomain),
						"NV" => __("Nevada", $this->pluginDomain),
						"NH" => __("New Hampshire", $this->pluginDomain),
						"NJ" => __("New Jersey", $this->pluginDomain),
						"NM" => __("New Mexico", $this->pluginDomain),
						"NY" => __("New York", $this->pluginDomain),
						"NC" => __("North Carolina", $this->pluginDomain),
						"ND" => __("North Dakota", $this->pluginDomain),
						"OH" => __("Ohio", $this->pluginDomain),
						"OK" => __("Oklahoma", $this->pluginDomain),
						"OR" => __("Oregon", $this->pluginDomain),
						"PA" => __("Pennsylvania", $this->pluginDomain),
						"RI" => __("Rhode Island", $this->pluginDomain),
						"SC" => __("South Carolina", $this->pluginDomain),
						"SD" => __("South Dakota", $this->pluginDomain),
						"TN" => __("Tennessee", $this->pluginDomain),
						"TX" => __("Texas", $this->pluginDomain),
						"UT" => __("Utah", $this->pluginDomain),
						"VT" => __("Vermont", $this->pluginDomain),
						"VA" => __("Virginia", $this->pluginDomain),
						"WA" => __("Washington", $this->pluginDomain),
						"WV" => __("West Virginia", $this->pluginDomain),
						"WI" => __("Wisconsin", $this->pluginDomain),
						"WY" => __("Wyoming", $this->pluginDomain),
					);
				      foreach ($states as $abbr => $fullname) {
				        print ("<option value=\"$abbr\" ");
				        if ($_EventState == $abbr) { 
				          print ('selected="selected" '); 
				        }
				        print (">$fullname</option>\n");
				      }
				      ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?php _e('Postal Code:',$this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventZip' name='EventZip' size='6' value='<?php echo $_EventZip; ?>' /></td>
		</tr>
		<tr id="google_map_link_toggle"<?php if( !sp_address_exists( $postId ) ) echo ' class="tec_hide"'; ?>>
			<td><?php _e('Show Google Map Link:',$this->pluginDomain); ?></td>
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
			<tr id="google_map_toggle"<?php if( !sp_address_exists( $postId ) ) echo ' class="tec_hide"'; ?>>
				<td><?php _e('Show Google Map:',$this->pluginDomain); ?></td>
				<td><input tabindex="<?php $this->tabIndex(); ?>" type="checkbox" id="EventShowMap" name="EventShowMap" size="6" value="true" <?php if( $tecNewPost || get_post_meta( $postId, '_EventShowMap', true ) == 'true' ) echo 'checked="checked"'; ?> /></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td><?php _e('Phone:',$this->pluginDomain); ?></td>
			<td><input tabindex="<?php $this->tabIndex(); ?>" type='text' id='EventPhone' name='EventPhone' size='14' value='<?php echo $_EventPhone; ?>' /></td>
		</tr>
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
		<tr class="eventBritePluginPlug">
			<td colspan="2" class="snp_sectionheader">
				<h4><?php _e('Sell Tickets &amp; Track Registration', $this->pluginDomain); ?></h4>	
			</td>
		</tr>
		<tr class="eventBritePluginPlug">
			<td colspan="2">
				<p><?php printf( __('Interested in selling tickets and tracking registrations? We have an add-on in the works that will integrate your events and sell tickets on <a href="%s">EventBrite</a>. <a href="%s">Stay Tuned!</a>', $this->pluginDomain ), 'http://www.eventbrite.com/r/simpleevents', $this->envatoUrl ); ?></a></p>
			</td>
		</tr>
		
		
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