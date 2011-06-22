<script type="text/javascript">
jQuery(document).ready(function($) {

	function displayOptionsError() {
		$.post('<?php bloginfo("wpurl"); ?>/wp-admin/admin-ajax.php', { action: 'getOptionsError' }, function(error) {
		  $('#tec-options-error').append('<h3>Error</h3><p>' + error + '</p>')
		});
	}

	// hide and show some defaults
	$('[name="eventsDefaultVenueID"]').change(function() {
		if($(this).find('option:selected').val() != "0") {
			$('.venue-default-info').hide();
		} else {
			$('.venue-default-info').show();
		}
	})
});
</script>
<style type="text/css">
div.snp_settings{
	width:90%;
}
</style>
<div class="snp_settings wrap">
<?php screen_icon(); ?><h2><?php printf( '%s Settings', $this->pluginName ); ?></h2>
<div id="tec-options-error" class="tec-events-error error"></div>
<?php
try {
	$hasDefaultVenue = sp_get_option('eventsDefaultVenueID') && sp_get_option('eventsDefaultVenueID') != "0";
	do_action( 'sp_events_options_top' );
	if ( !$this->optionsExceptionThrown ) {
		//TODO error saving is breaking options saving, to be fixed and uncommented later
		//$allOptions = $this->getOptions();
		//$allOptions['error'] = "";
		//$this->saveOptions( $allOptions );
	}
} catch( TEC_WP_Options_Exception $e ) {
	$this->optionsExceptionThrown = true;
	//$allOptions = $this->getOptions();
	//$allOptions['error'] = $e->getMessage();
	//$this->saveOptions( $allOptions );
	//$e->displayMessage(); //
}
?>
<div class="form">
	<h3><?php _e('Need a hand?',$this->pluginDomain); ?></h3>
	<p><?php printf( __( 'If you’re stuck on these options, please <a href="%s">check out the documentation</a>. Or, go to the <a href="%s">support forum</a>.', $this->pluginDomain ), trailingslashit($this->pluginUrl) . 'readme.txt', $this->supportUrl ); ?></p>
	<p><?php _e('Here is the iCal feed URL for your events: ' ,$this->pluginDomain); ?><code><?php echo sp_get_ical_link(); ?></code></p>

	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<?php wp_nonce_field('saveEventsCalendarOptions'); ?>

	<h3><?php _e('Settings', $this->pluginDomain); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Default View for the Events',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Default View for the Events',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Calendar'>
	                    <?php 
	                    $viewOptionValue = sp_get_option('viewOption','month');
							  $listViewStatus = ""; $gridViewStatus = "";
							  
	                    if( $viewOptionValue == 'upcoming' ) {
	                        $listViewStatus = 'checked="checked"';
	                    } else {
	                        $gridViewStatus = 'checked="checked"';
	                    }
	                    ?>
	                    <input type="radio" name="viewOption" value="month" <?php echo $gridViewStatus; ?> /> 
	                    <?php _e('Calendar',$this->pluginDomain); ?>
	                </label><br />
	                <label title='List View'>
	                    <input type="radio" name="viewOption" value="upcoming" <?php echo $listViewStatus; ?> /> 
	                    <?php _e('Event List',$this->pluginDomain); ?>
	                </label><br />
	            </fieldset>
	        </td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Show Comments',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Show Comments',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Yes'>
	                    <?php 
	                    $showCommentValue = sp_get_option('showComments','no');
							  $noCommentStatus = ""; $yesCommentStatus = "";
	                    if( $showCommentValue == 'no' ) {
	                        $noCommentStatus = 'checked="checked"';
	                    } else {
	                        $yesCommentStatus = 'checked="checked"';
	                    }
	                    ?>
	                    <input type="radio" name="showComments" value="yes" <?php echo $yesCommentStatus; ?> /> 
	                    <?php _e('Yes',$this->pluginDomain); ?>
	                </label><br />
	                <label title='Yes'>
	                    <input type="radio" name="showComments" value="no" <?php echo $noCommentStatus; ?> /> 
	                    <?php _e('No',$this->pluginDomain); ?>
	                </label><br />
	            </fieldset>
	        </td>
		</tr>
		<?php $multiDayCutoff = sp_get_option('multiDayCutoff','12:00'); ?>
		<tr>
			<th scope="row"><?php _e('Multiday Event Cutoff',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Multiday Event Cutoff',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Multiday Event Cutoff'>
							  <select name="multiDayCutoff">
								  <option <?php selected($multiDayCutoff == "12:00") ?> value="12:00" >12:00</option>
								  <option <?php selected($multiDayCutoff == "12:30") ?> value="12:30">12:30</option>
								  <?php for($i=1; $i < 23; $i++): ?>
									 <?php $val = (ceil($i/2) < 10 ? "0" : "") . ceil($i/2) . ":" . ($i % 2 == 1 ? "00" : "30" ); ?>
								    <option <?php selected($multiDayCutoff == $val) ?> value="<?php echo $val?>"><?php echo $val ?></option>
								  <?php endfor; ?>	
							  </select> AM
	                </label>
	            </fieldset>
					<div>
						<?php _e('For multi-day events, hide the last day from grid view if it ends on or before this time.',$this->pluginDomain); ?> 
					</div>				  
	        </td>
		</tr>		
			<?php 
			$embedGoogleMapsValue = sp_get_option('embedGoogleMaps','off');                 
	        ?>

		<tr>
			<th scope="row"><?php _e('Embed Google Maps',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Embed Google Maps',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Yes'>
	                    <?php 
	                    $embedGoogleMapsValue = sp_get_option('embedGoogleMaps','off'); 
								$embedGoogleMapsHeightValue = sp_get_option('embedGoogleMapsHeight','350');
								$embedGoogleMapsWidthValue = sp_get_option('embedGoogleMapsWidth','100%');
								$embedGoogleMapsOnStatus = ""; $embedGoogleMapsOffStatus = "";
	                    if( $embedGoogleMapsValue == 'on' ) {
	                        $embedGoogleMapsOnStatus = 'checked="checked"';
	                    } else {
	                        $embedGoogleMapsOffStatus = 'checked="checked"';
	                    }
	                    ?>
	                    <input type="radio" name="embedGoogleMaps" value="off" <?php echo $embedGoogleMapsOffStatus; ?> onClick="hidestuff('googleEmbedSize');" /> 
	                    <?php _e('Off',$this->pluginDomain); ?>
	                </label> 
	                <label title='List View'>
	                    <input type="radio" name="embedGoogleMaps" value="on" <?php echo $embedGoogleMapsOnStatus; ?> onClick="showstuff('googleEmbedSize');" /> 
	                    <?php _e('On',$this->pluginDomain); ?>
	                </label>
					<span id="googleEmbedSize" name="googleEmbedSize" style="margin-left:20px;" >
						<?php _e('Height',$this->pluginDomain); ?> <input type="text" name="embedGoogleMapsHeight" value="<?php echo $embedGoogleMapsHeightValue ?>" size=4>
						&nbsp;<?php _e('Width',$this->pluginDomain); ?> <input type="text" name="embedGoogleMapsWidth" value="<?php echo $embedGoogleMapsWidthValue ?>" size=4> <?php _e('(number or %)', $this->pluginDomain); ?>
					</span>
	<br />
	            </fieldset>
	        </td>
		</tr>

			<?php if( '' != get_option('permalink_structure') ) : ?>
			<tr>
				<th scope="row"><?php _e('Use Pretty URLs',$this->pluginDomain); ?></th>
		        <td>
		            <fieldset>
		                <legend class="screen-reader-text">
		                    <span><?php _e('Use Pretty URLs',$this->pluginDomain); ?></span>
		                </legend>
		                <label title='Yes'>
		                    <?php 
		                    $useRewriteRules = sp_get_option('useRewriteRules','on'); 
		                    ?>
		                    <input type="radio" name="useRewriteRules" value="off" <?php checked($useRewriteRules, 'off'); ?>  /> 
		                    <?php _e('Off',$this->pluginDomain); ?>
		                </label> 
		                <label title='List View'>
	                    <input type="radio" name="useRewriteRules" value="on" <?php checked($useRewriteRules, 'on'); ?>  /> 
		                    <?php _e('On',$this->pluginDomain); ?>
		                </label>
						<div>
							<?php _e('Although unlikely, pretty URLs (ie, http://site/events/upcoming) may interfere with custom themes or plugins.',$this->pluginDomain); ?> 
						</div>
		            </fieldset>
		        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Events URL slug', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Events URL slug', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="eventsSlug" value="<?php echo sp_get_option('eventsSlug', 'events') ?>" /> <?php _e('The slug used for building the Events URL.', $this->pluginDomain ) ?></label><br /><?php printf( __('Your current Events URL is <strong>%s</strong>', $this->pluginDomain ), sp_get_events_link() )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Single Event URL slug', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Single Event URL slug', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="singleEventSlug" value="<?php echo sp_get_option('singleEventSlug', 'event') ?>" /> <?php _e('The slug used for building a single Event URL.', $this->pluginDomain );  ?></label><br />
					<?php printf( __('<strong>NOTE:</strong> You <em>cannot</em> use the same slug as above. The above should ideally be plural, and this singular.<br />Your single Event URL is like: <strong>%s</strong>', $this->pluginDomain ), trailingslashit( home_url() ) . sp_get_option('singleEventSlug', 'event') . '/single-post-name/' ); ?>
				</fieldset></td>
			</tr>
			<?php endif; // permalink structure ?>
			<tr>
				<th scope="row"><?php _e('Debug', $this->pluginDomain ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Debug', $this->pluginDomain ); ?></legend>
					<label><input type="checkbox" name="spEventsDebug" value="on" <?php checked(sp_get_option('spEventsDebug'), 'on' ) ?> /> <?php _e('Debug Events display issues.', $this->pluginDomain ) ?></label>
					<div><?php _e('If you’re experiencing issues with posts not showing up in the admin, enable this option and then ensure that all of your posts have the correct start and end dates.', $this->pluginDomain) ?></div>
				</fieldset></td>
			</tr>
</table>

	<h3><?php _e('Theme Settings', $this->pluginDomain); ?></h3>
	<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Add HTML before calendar', $this->pluginDomain ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Add HTML before calendar', $this->pluginDomain ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsBeforeHTML"><?php echo  stripslashes(sp_get_option('spEventsBeforeHTML'));?></textarea>
					<div><?php _e('Some themes may require that you add extra divs before the calendar list to help with styling.', $this->pluginDomain);?> <?php _e('This is displayed directly after the header.', $this->pluginDomain);?> <?php  _e('You may use (x)HTML.', $this->pluginDomain) ?></div>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Add HTML after calendar', $this->pluginDomain ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Add HTML before calendar', $this->pluginDomain ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsAfterHTML"><?php echo stripslashes(sp_get_option('spEventsAfterHTML'));?></textarea>
					<div><?php _e('Some themes may require that you add extra divs after the calendar list to help with styling.', $this->pluginDomain);?> <?php _e('This is displayed directly above the footer.', $this->pluginDomain);?> <?php _e('You may use (x)HTML.', $this->pluginDomain) ?></div>
				</fieldset></td>
			</tr>
</table>

	<h3><?php _e('Customize Defaults', $this->pluginDomain); ?></h3>
	<p><?php _e('These settings change the default event form. For example, if you set a default venue, this field will be automatically filled in on a new event.', $this->pluginDomain) ?></p>
	<table class="form-table">
<tr>
			<th scope="row"><?php _e('Automatically replace empty fields with default values',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Automatically replace empty fields with default values',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Enable'>
	                    <?php 
	                    $defaultValueReplace = sp_get_option('defaultValueReplace','0');
							  $defaultValueReplaceEnabled = ""; $defaultValueReplaceDisabled = "";
	                    if( $defaultValueReplace == 1 ) {
	                        $defaultValueReplaceEnabled = 'checked="checked"';
	                    } else {
	                        $defaultValueReplaceDisabled = 'checked="checked"';
	                    }
	                    ?>
	                    <input type="radio" name="defaultValueReplace" value="1" <?php echo $defaultValueReplaceEnabled; ?> /> 
	                    <?php _e('Enabled',$this->pluginDomain); ?>
	                </label><br />
	                <label title='Disable'>
	                    <input type="radio" name="defaultValueReplace" value="0" <?php echo $defaultValueReplaceDisabled; ?> /> 
	                    <?php _e('Disabled',$this->pluginDomain); ?>
	                </label><br />
	            </fieldset>
	        </td>
		</tr>
			<tr>
				<th scope="row"><?php _e('Default Organizer for Events', $this->pluginDomain); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><?php _e('Default Organizer', $this->pluginDomain ); ?></legend>
					<label><?php $this->saved_organizers_dropdown(sp_get_option('eventsDefaultOrganizerID'),'eventsDefaultOrganizerID');?><?php _e('The default organizer value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultOrganizerID') )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Default Venue for Events', $this->pluginDomain); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><?php _e('Default Venue', $this->pluginDomain ); ?></legend>
					<label><?php $this->saved_venues_dropdown(sp_get_option('eventsDefaultVenueID'),'eventsDefaultVenueID');?><?php _e('The default venue value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultVenueID') )  ?>
				</fieldset></td>
			</tr>
			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
				<th scope="row"><?php _e('Default Address', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Address', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="eventsDefaultAddress" value="<?php echo sp_get_option('eventsDefaultAddress') ?>" /> <?php _e('The default address value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultAddress') )  ?>
				</fieldset></td>
			</tr>
			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
				<th scope="row"><?php _e('Default City', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default City', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="eventsDefaultCity" value="<?php echo sp_get_option('eventsDefaultCity') ?>" /> <?php _e('The default city value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultCity') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
				<th scope="row"><?php _e('Default State', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Province or State', $this->pluginDomain ); ?></legend>
					<label>
						<select id="eventsDefaultState" name='eventsDefaultState'>
							<option value=""><?php _e('Select a State:',$this->pluginDomain); ?></option>
							<?php
								foreach ($this->states as $abbr => $fullname) {
									print ("<option value=\"$abbr\" ");
									if (sp_get_option('eventsDefaultState') == $abbr) {
										print ('selected="selected" ');
									}
									print (">$fullname</option>\n");
								}
							?>
						</select>
						<?php _e('The default  value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultState') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
				<th scope="row"><?php _e('Default Province', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Province or State', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="eventsDefaultProvince" value="<?php echo sp_get_option('eventsDefaultProvince') ?>" /> <?php _e('The default  value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultProvince') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
				<th scope="row"><?php _e('Default Postal Code', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Postal Code', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="eventsDefaultZip" value="<?php echo sp_get_option('eventsDefaultZip') ?>" /> <?php _e('The default Postal Code value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultZip') )  ?>
				</fieldset></td>
			</tr>

			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
			<th scope="row"><?php _e('Default Country for Events',$this->pluginDomain); ?></th>
				<td>
					<select name="defaultCountry" id="defaultCountry">
							<?php 
							$this->constructCountries();
							$defaultCountry = sp_get_option('defaultCountry');
					foreach ($this->countries as $abbr => $fullname) {
						print ("<option value=\"$fullname\" ");
						if ($defaultCountry[1] == $fullname) { 
							print ('selected="selected" ');
						}
						print (">$fullname</option>\n");
					}
					?>
					</select>
				</td>
			</tr>
			<tr class="venue-default-info<?php echo $hasDefaultVenue ? " tec_hide" : "" ?>">
				<th scope="row"><?php _e('Default Phone', $this->pluginDomain); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Default Phone', $this->pluginDomain ); ?></legend>
					<label><input type="text" name="eventsDefaultPhone" value="<?php echo sp_get_option('eventsDefaultPhone') ?>" /> <?php _e('The default phone value', $this->pluginDomain ) ?></label><br /><?php printf( __('The current default value is <strong>%s</strong>', $this->pluginDomain ), sp_get_option('eventsDefaultPhone') )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Use a custom list of countries', $this->pluginDomain ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Use the following list:', $this->pluginDomain ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsCountries"><?php echo stripslashes(sp_get_option('spEventsCountries'));?></textarea>
					<div><?php _e('One country per line in the following format: <br/>US, United States <br/> UK, United Kingdom.', $this->pluginDomain);?> <?php _e('(Replaces the default list.)', $this->pluginDomain) ?></div>
				</fieldset></td>
			</tr>
	    <?php
		try {
			do_action( 'sp_events_options_bottom' );
			if ( !$this->optionsExceptionThrown ) {
				//TODO error saving is breaking options saving, to be fixed and uncommented later
				//$allOptions = $this->getOptions();
				//$allOptions['error'] = "";
				//$this->saveOptions( $allOptions );
			}
		} catch( TEC_WP_Options_Exception $e ) {
			$this->optionsExceptionThrown = true;
			//$allOptions = $this->getOptions();
			//$allOptions['error'] = $e->getMessage();
			//$this->saveOptions( $allOptions );
			//$e->displayMessage();
		}
		?>
		<tr>
	    	<td>
	    		<input id="saveEventsCalendarOptions" class="button-primary" type="submit" name="saveEventsCalendarOptions" value="<?php _e('Save Changes', $this->pluginDomain); ?>" />
	        </td>
	    </tr>
</table>

</form>

<?php
$old_events_posts = $this->getLegacyEvents();
$old_events = count($old_events_posts);

if ( $old_events ) {
	$old_events_copy = '<p class="message">' . sprintf( __('It looks like you have %s events in the category “%s”. Click below to import!', $this->pluginDomain ), $old_events, self::CATEGORYNAME ) . '</p>'; ?>
		
<form id="sp-upgrade" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<?php wp_nonce_field('upgradeEventsCalendar') ?>
	<h4><?php _e('Upgrade from The Events Calendar', $this->pluginDomain ); ?></h4>
	<p><?php _e('We built a vibrant community around our free <a href="http://wordpress.org/extend/plugins/the-events-calendar/" target="_blank">The Events Calendar</a> plugin. If you used the free version and are now using our premium version, thanks, we’re glad to have you here!', $this->pluginDomain ) ?></p>
	<?php echo $old_events_copy; ?>
	<input type="submit" value="Migrate Data!" class="button-secondary" name="upgradeEventsCalendar" />
</form>		
		
<?php
}

?>





<script>
function showstuff(boxid){
   document.getElementById(boxid).style.visibility="visible";
}

function hidestuff(boxid){
   document.getElementById(boxid).style.visibility="hidden";
}
<?php if( $embedGoogleMapsValue == 'off' ) { ?>
hidestuff('googleEmbedSize');
<?php }; ?>
</script>
</div>
</div>