<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function(){
		// Register event handler for the event toggle
		jQuery("input[name='isEvent']").click(function(){ 
			if ( jQuery(this).val() == 'yes' ) {
				jQuery("#eventDetails").slideDown(200);
				jQuery("#eventBriteTicketing").slideDown(200);
			} else {
				jQuery("#eventDetails").slideUp(200);
				jQuery("#eventBriteTicketing").slideUp(200);
			}
		});
		// toggle time input
		jQuery('#allDayCheckbox').click(function(){
			jQuery(".timeofdayoptions").toggle();
			jQuery("#EventTimeFormatDiv").toggle();
		});
		if( jQuery('#allDayCheckbox').attr('checked') == true ) {
			jQuery(".timeofdayoptions").addClass("tec_hide")
			jQuery("#EventTimeFormatDiv").addClass("tec_hide");
		}
		// Set the initial state of the event detail and EB ticketing div
		jQuery("input[name='isEvent']").each(function(){
			if( jQuery(this).val() == 'no' && jQuery(this).attr('checked') == true ) {
				jQuery('#eventDetails, #eventBriteTicketing').hide();
			} else if( jQuery(this).val() == 'yes' && jQuery(this).attr('checked') == true ) {
				jQuery('#eventDetails, #eventBriteTicketing').show();
			}
		});
		
		//show state/province input based on first option in countries list, or based on user input of country
		function spShowHideCorrectStateProvinceInput(country) {
			if (country == 'US') {
				jQuery("#USA").removeClass("tec_hide");
				jQuery("#International").addClass("tec_hide");
				jQuery('input[name="EventStateExists"]').val(1);
			} else if ( country != '' ) {
				jQuery("#International").removeClass("tec_hide");
				jQuery("#USA").addClass("tec_hide");
				jQuery('input[name="EventStateExists"]').val(0);			
			} else {
				jQuery("#International").addClass("tec_hide");
				jQuery("#USA").addClass("tec_hide");
				jQuery('input[name="EventStateExists"]').val(0);
			}
		}
		
		spShowHideCorrectStateProvinceInput( jQuery("#EventCountry > option:first").attr('label') );
		
		jQuery("#EventCountry").change(function() {
			var countryLabel = jQuery(this).find('option:selected').attr('label');
			jQuery('input[name="EventCountryLabel"]').val(countryLabel);
			spShowHideCorrectStateProvinceInput( countryLabel );
		});
		
		var spDaysPerMonth = [29,31,28,31,30,31,30,31,31,30,31,30,31];
		
		// start and end date select sections
		var spStartDays = [ jQuery('#28StartDays'), jQuery('#29StartDays'), jQuery('#30StartDays'), jQuery('#31StartDays') ];
		var spEndDays = [ jQuery('#28EndDays'), jQuery('#29EndDays'), jQuery('#30EndDays'), jQuery('#31EndDays') ];
				
		jQuery("select[name='EventStartMonth'], select[name='EventEndMonth']").change(function() {
			var t = jQuery(this);
			var startEnd = t.attr("name");
			// get changed select field
			if( startEnd == 'EventStartMonth' ) startEnd = 'Start';
			else startEnd = 'End';
			// show/hide date lists according to month
			var chosenMonth = t.attr("value");
			if( chosenMonth.charAt(0) == '0' ) chosenMonth = chosenMonth.replace('0', '');
			// leap year
			var remainder = jQuery("select[name='Event" + startEnd + "Year']").attr("value") % 4;
			if( chosenMonth == 2 && remainder == 0 ) chosenMonth = 0;
			// preserve selected option
			var currentDateField = jQuery("select[name='Event" + startEnd + "Day']");

			jQuery('.event' + startEnd + 'DateField').remove();
			if( startEnd == "Start") {
				var selectObject = spStartDays[ spDaysPerMonth[ chosenMonth ] - 28 ];
				selectObject.val( currentDateField.val() );
				jQuery("select[name='EventStartMonth']").after( selectObject );
			} else {
				var selectObject = spEndDays[ spDaysPerMonth[ chosenMonth ] - 28 ];
				selectObject.val( currentDateField.val() );
				jQuery('select[name="EventEndMonth"]').after( selectObject );
			}
		});
		
		jQuery("select[name='EventStartMonth'], select[name='EventEndMonth']").change();
		
		jQuery("select[name='EventStartYear']").change(function() {
			jQuery("select[name='EventStartMonth']").change();
		});
		
		jQuery("select[name='EventEndYear']").change(function() {
			jQuery("select[name='EventEndMonth']").change();
		});
		// hide / show google map toggles
		var tecAddressExists = false;
		var tecAddressInputs = ["EventAddress","EventCity","EventZip"];
		function tecShowHideGoogleMapToggles() {
			var selectValExists = false;
			var inputValExists = false;
				if(jQuery('input[name="EventCountryLabel"]').val()) selectValExists = true;
				jQuery.each( tecAddressInputs, function(key, val) {
					if( jQuery('input[name="' + val + '"]').val() ) {
						inputValExists = true;
						return false;
					}
				});
			if( selectValExists || inputValExists ) jQuery('tr#google_map_link_toggle,tr#google_map_toggle').removeClass('tec_hide');
			else jQuery('tr#google_map_link_toggle,tr#google_map_toggle').addClass('tec_hide');
		}
		jQuery.each( tecAddressInputs, function(key, val) {
			jQuery('input[name="' + val + '"]').bind('keyup', function(event) {
				var textLength = event.currentTarget.textLength;
				if(textLength == 0) tecShowHideGoogleMapToggles();
				else if(textLength == 1) tecShowHideGoogleMapToggles();
			});
		});
		jQuery('select[name="EventCountry"]').bind('change', function(event) {
			if(event.currentTarget.selectedIndex) tecShowHideGoogleMapToggles();
			else tecShowHideGoogleMapToggles();
		});
		tecShowHideGoogleMapToggles();
		// Form validation
		jQuery("form[name='post']").submit(function() {
			if( jQuery("#isEventNo").attr('checked') == true ) {
				// do not validate since this is not an event
				return true;
			}
			var event_phone = jQuery('#EventPhone');
			
			if( event_phone.length > 0 && event_phone.val().length && !event_phone.val().match(/^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/) ) {
				event_phone.focus();
				alert('<?php _e('Phone',$this->pluginDomain); ?> <?php _e('is not valid.', $this->pluginDomain); ?>  <?php _e('Valid values are local format (eg. 02 1234 5678 or 123 123 4567) or international format (eg. +61 (0) 2 1234 5678 or +1 123 123 4567).  You may also use an optional extension of up to five digits prefixed by x or ext (eg. 123 123 4567 x89)'); ?> ');
				return false;
			}
			return true;
		});
				
	});
</script>
<style type="text/css">
	.eventForm td {
		padding:6px 6px 0 0;
		font-size:11px;
		vertical-align:middle;
	}
	.eventForm select, .eventForm input {
		font-size:11px;
	}
	.eventForm h4 {
		font-size:1.2em;
		margin:2em 0 1em;
	}
	.eventForm h4.event-time {
		margin-top: 0;
	}
	.notice {
		background-color: rgb(255, 255, 224);
		border: 1px solid rgb(230, 219, 85);
		margin: 5px 0 15px;
	}
	#EventInfo {
		border-color:#dfdfdf;
		background-color:#F9F9F9;
		border-width:1px;
		border-style:solid;
		-moz-border-radius:3px;
		-khtml-border-radius:3px;
		-webkit-border-radius:3px;
		border-radius:3px;
		margin: 0;
		width:100%;
		border-style:solid;
		border-spacing:0;
		padding: 10px;
	}
	#eventIntro {
	  margin: 10px 0 25px 0;
	}
	
	.form-table form input {border:none;}
	<?php if( eventsGetOptionValue('donateHidden', false) ) : ?>
		#mainDonateRow {display: none;}
	<?php endif; ?>
	#submitLabel {display: block;}
	#submitLabel input {
		display: block;
		padding: 0;
	}
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
	<p>
		<?php _e('Is this post an event?',$this->pluginDomain); ?>&nbsp;
		<label><input tabindex="<?php $this->tabIndex(); ?>" type='radio' name='isEvent' value='yes' <?php echo $isEventChecked; ?> />&nbsp;<b><?php _e('Yes', $this->pluginDomain); ?></b></label>
		<label><input tabindex="<?php $this->tabIndex(); ?>" type='radio' name='isEvent' value='no' <?php echo $isNotEventChecked; ?> />&nbsp;<b><?php _e('No', $this->pluginDomain); ?></b></label>
	</p>
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
				<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartMonth'>
					<?php echo $startMonthOptions; ?>
				</select>
				<?php foreach( $startDayOptions as $key => $val ) : ?>
					<select id="<?php echo $key; ?>StartDays" class="eventStartDateField" tabindex="<?php $this->tabIndex(); ?>" name='EventStartDay'>
						<?php echo $val; ?>
					</select>
				<?php endforeach; ?>
				<select tabindex="<?php $this->tabIndex(); ?>" name='EventStartYear'>
					<?php echo $startYearOptions; ?>
				</select>
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
				<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndMonth'>
					<?php echo $endMonthOptions; ?>
				</select>
				<?php foreach( $endDayOptions as $key => $val ) : ?>
					<select id="<?php echo $key; ?>EndDays" class="eventEndDateField" tabindex="<?php $this->tabIndex(); ?>" name='EventEndDay'>
						<?php echo $val; ?>
					</select>
				<?php endforeach; ?>
				<select tabindex="<?php $this->tabIndex(); ?>" name='EventEndYear'>
					<?php echo $endYearOptions; ?>
				</select>
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
					$defaultCountry = eventsGetOptionValue('defaultCountry');
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
		<tr id="google_map_link_toggle"<?php if( !tec_address_exists( $postId ) ) echo ' class="tec_hide"'; ?>>
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
		<?php if( eventsGetOptionValue('embedGoogleMaps') == 'on' ) : ?>
			<tr id="google_map_toggle"<?php if( !tec_address_exists( $postId ) ) echo ' class="tec_hide"'; ?>>
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
				<p><?php _e('Interested in selling tickets and tracking registrations? Now you can do it for free using our <a href="http://wordpress.org/extend/plugins/eventbrite-for-the-events-calendar/">Eventbrite Integration Plugin</a>. Eventbrite is a feature rich easy-to-use event management tool. "Wow, you\'re selling Eventbrite pretty hard. You must get a kickback."  Well, now that you mention it... we do. We get a little something for everyone that registers an event using our referral link. It\'s how we\'re able to keep supporting and building plugins for the open source community. ', $this->pluginDomain); ?> <a href="http://www.eventbrite.com/r/simpleevents"><?php _e('Check it out here.', $this->pluginDomain); ?></a></p>
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
	<div id="mainDonateRow" class="eventForm">
			<?php _e('<h4>If You Like This Plugin - Help Support It</h4><p>We spend a lot of time and effort building robust plugins and we love to share them with the community. If you use this plugin consider making a donation to help support its\' continued development. You may remove this message on the <a href="/wp-admin/options-general.php?page=the-events-calendar.php">settings page</a>.</p>', $this->pluginDomain); ?>
				<div id="snp_thanks">
					<?php _e('Thanks', $this->pluginDomain); ?><br/>
					<h5 class="snp_brand">Shane &amp; Peter</h5>
					<a href="http://www.shaneandpeter.com?source=events-plugin" target="_blank">www.shaneandpeter.com</a>		
				</div>
				<div id="snp_donate">
					<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10750983&item_name=Events%20Post%20Editor" target="_blank">
						<image src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" alt="" />
					</a>
				</div>
		<div style="clear:both;"></div>
	</div><!-- end mainDonateRow -->
	<style>
	#eventDetails h4,
		#EventBriteDetailDiv h4 {
		text-transform: uppercase;
		border-bottom: 1px solid #e5e5e5;
		padding-bottom: 6px;
	}
	.eventForm td {
		padding-bottom: 10px !important;
		padding-top: 0 !important;
	}
	.eventForm .snp_sectionheader {
		padding-bottom: 5px !important;
	}
	#snp_thanks {
		float: left;
		width: 200px;
		margin: 5px 0 0 0;
	}
	.snp_brand {
		font-weight: normal;
		margin: 8px 0;
		font-family: Georgia !important;
		font-size: 17px !important;
	}
	.eventForm p {
		margin: 0 0 10px 0!important;
	}
	#eventDetails small,
		#EventBriteDetailDiv small {
		color: #a3a3a3;
		font-size: 10px;
	}
	#eventBriteTicketing,
		#mainDonateRow {
		background: url(<?php echo WP_PLUGIN_URL . '/the-events-calendar/resources/images/bg_fade.png';
		?>) repeat-x top left;
		background-color: #fff;
		padding: 10px 15px;
		border: 1px solid #e2e2e2;
		-moz-border-radius: 3px;
		-khtml-border-radius: 3px;
		-webkit-border-radius: 3px;
		-moz-border-radius-topleft: 0;
		-moz-border-radius-topright: 0;
		-webkit-border-top-left-radius: 0;
		-webkit-border-top-right-radius: 0;
		border-radius: 3px;
		margin: -11px 6px 0;
	}
	#eventBriteTicketing h2 {
		background: url(<?php echo WP_PLUGIN_URL . '/the-events-calendar/resources/images/eb_press_little.gif';
		?>) no-repeat top right;
		height: 80px;
		margin: 0;
	}
	.eventForm {
		margin-top: -20px;
	}
	#EventInfo,
		table.eventForm {
		width: 100%;
	}
	td.snp_message {
		padding-bottom: 10px !important;
	}
	</style>

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