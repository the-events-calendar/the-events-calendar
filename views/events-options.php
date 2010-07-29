<script type="text/javascript">
jQuery(document).ready(function($) {

	function displayOptionsError() {
		$.post('<?php bloginfo("wpurl"); ?>/wp-admin/admin-ajax.php', { action: 'getOptionsError' }, function(error) {
		  $('#tec-options-error').append('<h3>Error</h3><p>' + error + '</p>')
		});
	}
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
	<p><?php printf( __( 'If you’re stuck on these options, please <a href="%s">check out the documentation</a>. Or, go to the <a href="%s">support forum</a>.', $this->pluginDomain ), $this->pluginUrl . '/readme.txt', $this->supportUrl ); ?></p>
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
	    <tr>
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


			<?php 
			$embedGoogleMapsValue = sp_get_option('embedGoogleMaps','off');                 
	        ?>
<?php /* 
		<tr>
			<th scope="row"><?php _e('Display Events on Homepage',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Display Events on Homepage',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Yes'>
	                    <?php 
	                    $displayEventsOnHomepage = sp_get_option('displayEventsOnHomepage','on'); 
	                    ?>
	                    <input type="radio" name="displayEventsOnHomepage" value="off" <?php checked($displayEventsOnHomepage, 'off'); ?>  /> 
	                    <?php _e('Off',$this->pluginDomain); ?>
	                </label> 
	                <label title='List View'>
                    <input type="radio" name="displayEventsOnHomepage" value="on" <?php checked($displayEventsOnHomepage, 'on'); ?>  /> 
	                    <?php _e('On',$this->pluginDomain); ?>
	                </label>
	            </fieldset>
	        </td>
		</tr>
*/ ?>
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
<?php /*
			<tr>
				<th scope="row"><?php _e('Feature on Event Date',$this->pluginDomain); ?></th>
		        <td>
		            <fieldset>
		                <legend class="screen-reader-text">
		                    <span><?php _e('Feature on Event Date',$this->pluginDomain); ?></span>
		                </legend>
		                <label title='Yes'>
		                    <?php 
		                    $resetEventPostDate = sp_get_option('resetEventPostDate','off'); 
		                    ?>
		                    <input type="radio" name="resetEventPostDate" value="off" <?php checked($resetEventPostDate, 'off'); ?>  /> 
		                    <?php _e('Off',$this->pluginDomain); ?>
		                </label> 
		                <label title='List View'>
	                    <input type="radio" name="resetEventPostDate" value="on" <?php checked($resetEventPostDate, 'on'); ?>  /> 
		                    <?php _e('On',$this->pluginDomain); ?>
		                </label>
						<div>
							<?php _e('This option will bump an event to the top of the homepage loop on the day of the event.',$this->pluginDomain); ?> 
						</div>
		<br />
		            </fieldset>
		        </td>
			</tr>
*/ ?>
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
			<?php endif; // permalink structure ?>
			<tr>
				<th scope="row"><?php _e('Debug', $this->pluginDomain ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Debug', $this->pluginDomain ); ?></legend>
					<label><input type="checkbox" name="spEventsDebug" <?php checked(sp_get_option('spEventsDebug'), 'on' ) ?> /> <?php _e('Debug Events display issues.') ?></label>
					<div><?php _e('If you’re experiencing issues with posts not showing up in the admin, enable this option and then ensure that all of your posts have the correct start and end dates.', $this->pluginDomain) ?></div>
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