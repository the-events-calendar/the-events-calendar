<script type="text/javascript">
jQuery(document).ready(function() {

	function theEventsCalendarHideDonateButton() {
		jQuery('#mainDonateRow').hide();
		jQuery('#secondDonateRow').show();
	} 
	jQuery('#hideDonateButton').click(function() {
		jQuery.post( '<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php', { donateHidden: true, action: 'hideDonate' }, theEventsCalendarHideDonateButton, 'json' );
	});

	function displayOptionsError() {
		$.post('<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php', { action: 'getOptionsError' }, function(error) {
		  $('#tec-options-error').append('<h3>Error</h3><p>' + error + '</p>')
		});
	}
});
</script>
<style type="text/css">
.form-table form input {border:none;}
<?php if( eventsGetOptionValue('donateHidden', false) ) : ?>
	#mainDonateRow {display: none;}
<?php else : ?>
	#mainDonateRow {background-color: #FCECA9;}
	#secondDonateRow {display: none;}
<?php endif; ?>
#mainDonateRow label {}
#submitLabel {display: block;}
#submitLabel input {
	display: block;
	padding: 0;
}
#hideDonateButton {}
#checkBoxLabel {}
.form-table form #secondSubmit {
	background:#f2f2f2 url(<?php bloginfo('wpurl'); ?>/images/white-grad-active.png) repeat-x scroll left top;
	text-decoration: none;
	font-size: 11px;
	line-height: 16px;
	padding: 5px 11px;
	margin-bottom:10px;
	cursor: pointer;
	border: 1px solid #bbb;
	-moz-border-radius: 5px;
	-khtml-border-radius: 5px;
	-webkit-border-radius: 5px;
	border-radius: 5px;
	-moz-box-sizing: content-box;
	-webkit-box-sizing: content-box;
	-khtml-box-sizing: content-box;
	box-sizing: content-box;
	text-shadow: rgba(255,255,255,1) 0 1px 0;
	color: #6b6b6b;
	font-weight: bold;
	text-transform: uppercase;
}
.form-table form #secondSubmit {
	background: #f2f2f2 url(<?php bloginfo('wpurl'); ?>/wp-admin/images/white-grad.png) repeat-x scroll left top;
}

.form-table form #secondSubmit:active {
	background: #eee url(<?php bloginfo('wpurl'); ?>/wp-admin/images/white-grad-active.png) repeat-x scroll left top;
}

.form-table form #secondSubmit:hover {
	color: #000;
	border-color: #666;
}
div.snp_settings{
	width:90%;
}
</style>
<div class="snp_settings wrap">
<h2><?php _e('The Events Calendar Settings',$this->pluginDomain); ?></h2>
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
	<p><?php _e('If you\'re stuck on these options, please <a href="http://wordpress.org/extend/plugins/the-events-calendar/">check out the documentation</a>. If you\'re still wondering what\'s going on, be sure to stop by the support <a href="http://wordpress.org/tags/the-events-calendar?forum_id=10">forum</a> and ask for help. The open source community is full of kind folks who are happy to help.',$this->pluginDomain); ?></p>
	<p><?php _e('Here is the iCal feed URL for your events: ' ,$this->pluginDomain); ?><code><?php bloginfo('home'); ?>/?ical</code></p>
	<table class="form-table">
	    <tr id="mainDonateRow">
	    	<th scope="row"><?php _e('Donate',$this->pluginDomain); ?></th>
	        <td>
	            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	                <input type="hidden" name="cmd" value="_s-xclick">
	                <input type="hidden" name="hosted_button_id" value="10750983">
	                <input type="hidden" name="item_name" value="Events Options Panel Main">
	                <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	                <label id="submitLabel" for="submit">
	                	<?php _e('If you find this plugin useful, please consider donating to the producer of it, Shane &#38; Peter, Inc. Thank you!',$this->pluginDomain); ?>
	                </label>

	                <input id="hideDonateButton" type="checkbox" name="hideDonateButton" value="" />
	                <label id="checkBoxLabel" class="button" for="hideDonateButton"><?php _e('I have already donated, so please hide this button!',$this->pluginDomain); ?></label>
	            </form>
	        </td>
	    </tr>
	    <tr id="secondDonateRow">
	        <td>
	            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	                <input type="hidden" name="cmd" value="_s-xclick">
	                <input type="hidden" name="hosted_button_id" value="10751527">
	                <input type="hidden" name="item_name" value="Events Options Panel Secondary">
	                <input id="secondSubmit" type="submit" value="<?php _e('Donate for this wonderful plugin', $this->pluginDomain); ?>" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	            </form>
	        </td>
	    </tr>
	</table>

	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

	<?php
	if ( function_exists('wp_nonce_field') ) {
		wp_nonce_field('saveEventsCalendarOptions');
	}
	?>
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
	                    $viewOptionValue = eventsGetOptionValue('viewOption','month'); 
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
	                    $showCommentValue = eventsGetOptionValue('showComments','no'); 
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
					$defaultCountry = eventsGetOptionValue('defaultCountry');
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
			$embedGoogleMapsValue = eventsGetOptionValue('embedGoogleMaps','off');                 
	        ?>
		<tr>
			<th scope="row"><?php _e('Display Events on Homepage',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Display Events on Homepage',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Yes'>
	                    <?php 
	                    $displayEventsOnHomepage = eventsGetOptionValue('displayEventsOnHomepage','on'); 
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

		<tr>
			<th scope="row"><?php _e('Embed Google Maps',$this->pluginDomain); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Embed Google Maps',$this->pluginDomain); ?></span>
	                </legend>
	                <label title='Yes'>
	                    <?php 
	                    $embedGoogleMapsValue = eventsGetOptionValue('embedGoogleMaps','off'); 
	 					$embedGoogleMapsHeightValue = eventsGetOptionValue('embedGoogleMapsHeight','350'); 
	 					$embedGoogleMapsWidthValue = eventsGetOptionValue('embedGoogleMapsWidth','100%'); 
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

			<tr>
				<th scope="row"><?php _e('Feature on Event Date',$this->pluginDomain); ?></th>
		        <td>
		            <fieldset>
		                <legend class="screen-reader-text">
		                    <span><?php _e('Feature on Event Date',$this->pluginDomain); ?></span>
		                </legend>
		                <label title='Yes'>
		                    <?php 
		                    $resetEventPostDate = eventsGetOptionValue('resetEventPostDate','off'); 
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
		                    $useRewriteRules = eventsGetOptionValue('useRewriteRules','on'); 
		                    ?>
		                    <input type="radio" name="useRewriteRules" value="off" <?php checked($useRewriteRules, 'off'); ?>  /> 
		                    <?php _e('Off',$this->pluginDomain); ?>
		                </label> 
		                <label title='List View'>
	                    <input type="radio" name="useRewriteRules" value="on" <?php checked($useRewriteRules, 'on'); ?>  /> 
		                    <?php _e('On',$this->pluginDomain); ?>
		                </label>
						<div>
							<?php _e('Pretty URLs (ie, http://site/category/events/upcoming) may interfere with custom themes or plugins.',$this->pluginDomain); ?> 
						</div>
		<br />
		            </fieldset>
		        </td>
			</tr>
			<?php endif; // permalink structure ?>
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