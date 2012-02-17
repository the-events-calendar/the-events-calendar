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
<?php screen_icon(); ?><h2><?php printf( __('%s Settings', 'tribe-events-calendar'), $this->pluginName ); ?></h2>
<div id="tribe-events-options-error" class="tribe-events-error error"></div>
<?php $this->do_action( 'tribe_events_options_top' ); ?>
<div class="form">
	<h3><?php _e('Need a hand?','tribe-events-calendar'); ?></h3>
	<p><?php printf( __( 'If you\'re stuck on these options, please go to the <a href="%s">support forum</a>.', 'tribe-events-calendar' ), TribeEvents::$tribeUrl.TribeEvents::$supportPath ); ?></p>
   <?php if( function_exists( 'tribe_get_ical_link' ) ): ?>
      <p><?php _e('Here is the iCal feed URL for your events: ' ,'tribe-events-calendar'); ?><code><?php echo tribe_get_ical_link(); ?></code></p>
   <?php endif; ?>

	<form method="post">
	<?php wp_nonce_field('saveEventsCalendarOptions'); ?>
	<?php do_action('tribe-events-settings-top'); ?>
	<h3><?php _e('Settings', 'tribe-events-calendar'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Default View for the Events','tribe-events-calendar'); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Default View for the Events','tribe-events-calendar'); ?></span>
	                </legend>
	                <label title='Calendar'>
	                    <input type="radio" name="viewOption" value="month" <?php checked( tribe_get_option('viewOption','month'), 'month' ); ?> /> 
	                    <?php _e('Calendar','tribe-events-calendar'); ?>
	                </label><br />
	                <label title='List View'>
	                    <input type="radio" name="viewOption" value="upcoming" <?php checked( tribe_get_option('viewOption','month'), 'upcoming' ); ?> /> 
	                    <?php _e('Event List','tribe-events-calendar'); ?>
	                </label><br />
	            </fieldset>
	        </td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Number of events to show per page in the loop','tribe-events-calendar'); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text">
						<span><?php _e('Number of events to show per page in the loop','tribe-events-calendar'); ?></span>
					</legend>
					<label><input type="text" name="postsPerPage" size="4" value="<?php echo esc_attr( tribe_get_option('postsPerPage', 10) ) ?>" /></label>
				</fieldset>
				<div>
					<?php _e('This is the number of posts displayed per page when returning a list of events.','tribe-events-calendar'); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Show Comments','tribe-events-calendar'); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Show Comments','tribe-events-calendar'); ?></span>
	                </legend>
	                <label title='<?php _e('Show Comments','tribe-events-calendar'); ?>'>
	                    <input type="checkbox" name="showComments" value="1" <?php checked( tribe_get_option('showComments') ) ?> />
	                </label>
	            </fieldset>
	        </td>
		</tr>
		<?php $multiDayCutoff = tribe_get_option('multiDayCutoff','12:00'); ?>
		<tr>
			<th scope="row"><?php _e('Multiday Event Cutoff','tribe-events-calendar'); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Multiday Event Cutoff','tribe-events-calendar'); ?></span>
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
						<?php _e('For multi-day events, hide the last day from grid view if it ends on or before this time.','tribe-events-calendar'); ?> 
					</div>				  
	        </td>
		</tr>
		<tr>
			<th scope="row"><?php _e('Enable Google Maps','tribe-events-calendar'); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('Enable Google Maps','tribe-events-calendar'); ?></span>
	                </legend>
	                <label title='Enable Google Maps'>
						<input type="checkbox" id="embedGoogleMaps" name="embedGoogleMaps" value="1" <?php checked( tribe_get_option('embedGoogleMaps') ); ?>/>
	                </label>
					<span id="googleEmbedSize" name="googleEmbedSize" style="margin-left:20px;" >
						<?php _e('Height','tribe-events-calendar'); ?> <input type="text" name="embedGoogleMapsHeight" value="<?php echo esc_attr( tribe_get_option('embedGoogleMapsHeight','350') ); ?>" size=4>
						&nbsp;<?php _e('Width','tribe-events-calendar'); ?> <input type="text" name="embedGoogleMapsWidth" value="<?php echo esc_attr( tribe_get_option('embedGoogleMapsWidth','100%') ); ?>" size=4> <?php _e('(number or %)', 'tribe-events-calendar'); ?>
						&nbsp;<?php _e('Zoom Level', 'tribe-events-calendar'); ?> <input type="text" name="embedGoogleMapsZoom" value="<?php echo esc_attr( tribe_get_option('embedGoogleMapsZoom','10') ); ?>" size=2> <?php  _e('(0 = zoomed-out; 21 = zoomed-in)', 'tribe-events-calendar'); ?>
					</span>
	<br />
					<div>
						<?php _e('If you don\'t have this turned on, your event listings won\'t have the backend map preview or frontend embedded map.','tribe-events-calendar'); ?> 
					<div>
	            </fieldset>
	        </td>
		</tr>

			<?php if( '' != get_option('permalink_structure') ) : ?>
			<tr>
				<th scope="row"><?php _e('Events URL slug', 'tribe-events-calendar'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Events URL slug', 'tribe-events-calendar' ); ?></legend>
					<label><input type="text" name="eventsSlug" value="<?php echo esc_attr( tribe_get_option('eventsSlug', 'events') ) ?>" /> <?php _e('The slug used for building the Events URL.', 'tribe-events-calendar' ) ?></label><br /><?php printf( __('Your current Events URL is <strong><a href="%s">%s</a></strong>', 'tribe-events-calendar' ), tribe_get_events_link(), tribe_get_events_link() )  ?>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Single Event URL slug', 'tribe-events-calendar'); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Single Event URL slug', 'tribe-events-calendar' ); ?></legend>
					<label><input type="text" name="singleEventSlug" value="<?php echo esc_attr( tribe_get_option('singleEventSlug', 'event') ) ?>" /> <?php _e('The slug used for building a single Event URL.', 'tribe-events-calendar' );  ?></label><br />
					<?php printf( __('<strong>NOTE:</strong> You <em>cannot</em> use the same slug as above. The above should ideally be plural, and this singular.<br />Your single Event URL is like: <strong>%s</strong>', 'tribe-events-calendar' ), trailingslashit( home_url() ) . tribe_get_option('singleEventSlug', 'event') . '/single-post-name/' ); ?>
				</fieldset></td>
			</tr>
			<?php endif; // permalink structure ?>
			<tr>
				<th scope="row"><?php _e('Debug', 'tribe-events-calendar' ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Debug', 'tribe-events-calendar' ); ?></legend>
					<label><input type="checkbox" name="debugEvents" value="1" <?php checked( tribe_get_option('debugEvents') ) ?> /> <?php _e('Debug Events display issues.', 'tribe-events-calendar' ) ?></label>
					<div><?php printf(__('Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the <a href="%s" target="_blank">Debug Bar Plugin</a> and look for the "Tribe" tab in the debug output.', 'tribe-events-calendar'),'http://wordpress.org/extend/plugins/debug-bar/'); ?></div>
				</fieldset></td>
			</tr>
</table>

	<h3><?php _e('Theme Settings', 'tribe-events-calendar'); ?></h3>
	<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Events Template', 'tribe-events-calendar' ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Events Template', 'tribe-events-calendar' ); ?></legend>
					<select name="spEventsTemplate">
						<option value=''><?php _e('Default Events Template', 'tribe-events-calendar' ); ?></option>
						<option value='default' <?php selected(tribe_get_option('spEventsTemplate', 'default') == 'default') ?>><?php _e('Default Page Template', 'tribe-events-calendar' ); ?></option>
						<?php page_template_dropdown(tribe_get_option('spEventsTemplate', 'default')); ?>
					</select>
					<div><?php _e('Choose a page template to control the look and feel of your calendar.', 'tribe-events-calendar');?> </div>
				</fieldset></td>
			</tr>		
			<tr>
				<th scope="row"><?php _e('Add HTML before calendar', 'tribe-events-calendar' ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Add HTML before calendar', 'tribe-events-calendar' ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsBeforeHTML"><?php echo  stripslashes(tribe_get_option('spEventsBeforeHTML'));?></textarea>
					<div><?php _e('Some themes may require that you add extra divs before the calendar list to help with styling.', 'tribe-events-calendar');?> <?php _e('This is displayed directly after the header.', 'tribe-events-calendar');?> <?php  _e('You may use (x)HTML.', 'tribe-events-calendar') ?></div>
				</fieldset></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Add HTML after calendar', 'tribe-events-calendar' ); ?></th>
				<td><fieldset>
					<legend class="screen-reader-text"><?php _e('Add HTML before calendar', 'tribe-events-calendar' ); ?></legend>
					<textarea style="width:100%; height:100px;" name="spEventsAfterHTML"><?php echo stripslashes(tribe_get_option('spEventsAfterHTML'));?></textarea>
					<div><?php _e('Some themes may require that you add extra divs after the calendar list to help with styling.', 'tribe-events-calendar');?> <?php _e('This is displayed directly above the footer.', 'tribe-events-calendar');?> <?php _e('You may use (x)HTML.', 'tribe-events-calendar') ?></div>
				</fieldset></td>
			</tr>
</table>
   <?php do_action('tribe-events-after-theme-settings'); ?>
	<?php $this->do_action( 'tribe_events_options_bottom' ); ?>
	<table>
		<tr>
	    	<td>
	    		<input id="saveEventsCalendarOptions" class="button-primary" type="submit" name="saveEventsCalendarOptions" value="<?php _e('Save Changes', 'tribe-events-calendar'); ?>" />
	        </td>
	    </tr>	
	 </table>
</form>

<?php $this->do_action( 'tribe_events_options_post_form' ); ?>
</div>
</div>
