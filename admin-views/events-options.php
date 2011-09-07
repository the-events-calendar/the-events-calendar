<?php
/**
* Settings panel
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>

<script type="text/javascript">
jQuery(document).ready(function($) {

	// toggle view of the venue defaults fields
	$('[name="eventsDefaultVenueID"]').change(function() {
		updateVenueFields();
	})
	function updateVenueFields() {
		if($('[name="eventsDefaultVenueID"]').find('option:selected').val() != "0") {
			$('.venue-default-info').hide();
		} else {
			$('.venue-default-info').show();
		}		
	}
	updateVenueFields();

	// toggle view of the google maps size fields
	$('#embedGoogleMaps').change(function() {
		updateMapsFields();
	})
	function updateMapsFields() {
		if($('#embedGoogleMaps').attr("checked")) {
			$('#googleEmbedSize').show();
		} else {
			$('#googleEmbedSize').hide();			
		}
	}
	updateMapsFields();
});
</script>
<style type="text/css">
div.tribe_settings{
	width:90%;
}
</style>
<div class="tribe_settings wrap">
<?php screen_icon(); ?><h2><?php printf( __('%s Settings', self::PLUGIN_DOMAIN), $this->pluginName ); ?></h2>
<div id="tribe-events-options-error" class="tribe-events-error error"></div>
<?php $this->do_action( 'tribe_events_options_top' ); ?>
<div class="form">
	<h3><?php _e('Need a hand?',self::PLUGIN_DOMAIN); ?></h3>
	<p><?php printf( __( 'If you\'re stuck on these options, please <a href="%s">check out the documentation</a>. Or, go to the <a href="%s">support forum</a>.', self::PLUGIN_DOMAIN ), trailingslashit($this->pluginUrl) . 'readme.txt', $this->supportUrl ); ?></p>
	<p><?php _e('Here is the iCal feed URL for your events: ' ,self::PLUGIN_DOMAIN); ?><code><?php echo tribe_get_ical_link(); ?></code></p>

	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<?php wp_nonce_field('saveEventsCalendarOptions'); ?>

	<h3><?php _e('Settings', self::PLUGIN_DOMAIN); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Default View for the Events',self::PLUGIN_DOMAIN); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Default View for the Events',self::PLUGIN_DOMAIN); ?></span>
	                </legend>
	                <label title='Calendar'>
	                    <input type="radio" name="viewOption" value="month" <?php checked( tribe_get_option('viewOption','month'), 'month' ); ?> /> 
	                    <?php _e('Calendar',self::PLUGIN_DOMAIN); ?>
	                </label><br />
	                <label title='List View'>
	                    <input type="radio" name="viewOption" value="upcoming" <?php checked( tribe_get_option('viewOption','month'), 'upcoming' ); ?> /> 
	                    <?php _e('Event List',self::PLUGIN_DOMAIN); ?>
	                </label><br />
	            </fieldset>
	        </td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Show Comments',self::PLUGIN_DOMAIN); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Show Comments',self::PLUGIN_DOMAIN); ?></span>
	                </legend>
	                <label title='<?php _e('Show Comments',self::PLUGIN_DOMAIN); ?>'>
	                    <input type="checkbox" name="showComments" value="1" <?php checked( tribe_get_option('showComments') ) ?> />
	                </label>
	            </fieldset>
	        </td>
		</tr>
		<?php $multiDayCutoff = tribe_get_option('multiDayCutoff','12:00'); ?>
		<tr>
			<th scope="row"><?php _e('Multiday Event Cutoff',self::PLUGIN_DOMAIN); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Multiday Event Cutoff',self::PLUGIN_DOMAIN); ?></span>
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
						<?php _e('For multi-day events, hide the last day from grid view if it ends on or before this time.',self::PLUGIN_DOMAIN); ?> 
					</div>				  
	        </td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Enable Google Maps',self::PLUGIN_DOMAIN); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Enable Google Maps',self::PLUGIN_DOMAIN); ?></span>
	                </legend>
	                <label title='Enable Google Maps'>
						<input type="checkbox" id="embedGoogleMaps" name="embedGoogleMaps" value="1" <?php checked( tribe_get_option('embedGoogleMaps') ); ?>/>
	                </label>
					<span id="googleEmbedSize" name="googleEmbedSize" style="margin-left:20px;" >
						<?php _e('Height',self::PLUGIN_DOMAIN); ?> <input type="text" name="embedGoogleMapsHeight" value="<?php echo tribe_get_option('embedGoogleMapsHeight','350'); ?>" size=4>
						&nbsp;<?php _e('Width',self::PLUGIN_DOMAIN); ?> <input type="text" name="embedGoogleMapsWidth" value="<?php echo tribe_get_option('embedGoogleMapsWidth','100%'); ?>" size=4> <?php _e('(number or %)', self::PLUGIN_DOMAIN); ?>
					</span>
	<br />
					<div>
						<?php _e('If you don\'t have this turned on, your event listings won\'t have the backend map preview or frontend embedded map.',self::PLUGIN_DOMAIN); ?> 
					<div>
	            </fieldset>
	        </td>
		</tr>

			<?php if( '' != get_option('permalink_structure') ) : ?>
			<tr>
				<th scope="row"><?php _e('Use Pretty URLs',self::PLUGIN_DOMAIN); ?></th>
		        <td>
		            <fieldset>
		                <legend class="screen-reader-text">
		                    <span><?php _e('Use Pretty URLs',self::PLUGIN_DOMAIN); ?></span>
		                </legend>
		                <label title='Use Rewrite Rules'>
		                    <input type="checkbox" name="useRewriteRules" value="1" <?php checked( tribe_get_option('useRewriteRules', 1) ); ?>  />
		                </label>
						<div>
							<?php _e('Although unlikely, pretty URLs (ie, http://site/events/upcoming) may interfere with custom themes or plugins.',self::PLUGIN_DOMAIN); ?> 
						</div>
		            </fieldset>
		        </td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Events URL slug', self::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Events URL slug', self::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="eventsSlug" value="<?php echo tribe_get_option('eventsSlug', 'events') ?>" /> <?php _e('The slug used for building the Events URL.', self::PLUGIN_DOMAIN ) ?></label><br /><?php printf( __('Your current Events URL is <strong>%s</strong>', self::PLUGIN_DOMAIN ), tribe_get_events_link() )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Single Event URL slug', self::PLUGIN_DOMAIN); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Single Event URL slug', self::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="text" name="singleEventSlug" value="<?php echo tribe_get_option('singleEventSlug', 'event') ?>" /> <?php _e('The slug used for building a single Event URL.', self::PLUGIN_DOMAIN );  ?></label><br />
					<?php printf( __('<strong>NOTE:</strong> You <em>cannot</em> use the same slug as above. The above should ideally be plural, and this singular.<br />Your single Event URL is like: <strong>%s</strong>', self::PLUGIN_DOMAIN ), trailingslashit( home_url() ) . tribe_get_option('singleEventSlug', 'event') . '/single-post-name/' ); ?>
				</fieldset></td>
			</tr>
			<?php endif; // permalink structure ?>
			<tr>
				<th scope="row"><?php _e('Debug', self::PLUGIN_DOMAIN ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Debug', self::PLUGIN_DOMAIN ); ?></legend>
					<label><input type="checkbox" name="debugEvents" value="1" <?php checked( tribe_get_option('debugEvents') ) ?> /> <?php _e('Debug Events display issues.', self::PLUGIN_DOMAIN ) ?></label>
					<div><?php printf(__('Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the <a href="%s" target="_blank">Debug Bar Plugin</a> and look for the "Tribe" tab in the debug output.', self::PLUGIN_DOMAIN),'http://wordpress.org/extend/plugins/debug-bar/'); ?></div>
				</fieldset></td>
			</tr>
</table>

	<h3><?php _e('Theme Settings', self::PLUGIN_DOMAIN); ?></h3>
	<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Events Template', self::PLUGIN_DOMAIN ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Events Template', self::PLUGIN_DOMAIN ); ?></legend>
					<select name="spEventsTemplate">
						<option value=''><?php _e('Default ECP Template'); ?></option>
						<option value='default' <?php selected(tribe_get_option('spEventsTemplate', 'default') == 'default') ?>><?php _e('Default Page Template'); ?></option>
						<?php page_template_dropdown(tribe_get_option('spEventsTemplate', 'default')); ?>
					</select>
					<div><?php _e('Choose a page template to control the look and feel of your calendar.', self::PLUGIN_DOMAIN);?> </div>
				</fieldset></td>
			</tr>		
			<tr>
				<th scope="row"><?php _e('Add HTML before calendar', self::PLUGIN_DOMAIN ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Add HTML before calendar', self::PLUGIN_DOMAIN ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsBeforeHTML"><?php echo  stripslashes(tribe_get_option('spEventsBeforeHTML'));?></textarea>
					<div><?php _e('Some themes may require that you add extra divs before the calendar list to help with styling.', self::PLUGIN_DOMAIN);?> <?php _e('This is displayed directly after the header.', self::PLUGIN_DOMAIN);?> <?php  _e('You may use (x)HTML.', self::PLUGIN_DOMAIN) ?></div>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Add HTML after calendar', self::PLUGIN_DOMAIN ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Add HTML before calendar', self::PLUGIN_DOMAIN ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsAfterHTML"><?php echo stripslashes(tribe_get_option('spEventsAfterHTML'));?></textarea>
					<div><?php _e('Some themes may require that you add extra divs after the calendar list to help with styling.', self::PLUGIN_DOMAIN);?> <?php _e('This is displayed directly above the footer.', self::PLUGIN_DOMAIN);?> <?php _e('You may use (x)HTML.', self::PLUGIN_DOMAIN) ?></div>
				</fieldset></td>
			</tr>
</table>
   <?php do_action('tribe-events-after-theme-settings'); ?>
	<?php $this->do_action( 'tribe_events_options_bottom' ); ?>
	<table>
		<tr>
	    	<td>
	    		<input id="saveEventsCalendarOptions" class="button-primary" type="submit" name="saveEventsCalendarOptions" value="<?php _e('Save Changes', self::PLUGIN_DOMAIN); ?>" />
	        </td>
	    </tr>	
	 </table>
</form>

<?php $this->do_action( 'tribe_events_options_post_form' ); ?>
</div>
</div>
