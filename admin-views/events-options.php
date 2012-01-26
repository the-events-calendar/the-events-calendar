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
<?php $tab = ( isset($_GET['tab']) && $_GET['tab'] ) ? esc_attr($_GET['tab']) : 'general'; ?>
<?php screen_icon(); ?><h2><?php printf( __('%s Settings', 'tribe-events-calendar'), $this->pluginName ); ?></h2>
<div id="tribe-events-options-error" class="tribe-events-error error"></div>
<?php $this->settingsTabs( $tab );
$this->do_action( 'tribe_events_options_top' ); ?>
<div class="form">
<?php $this->do_action('tribe-events-before-'.$tab.'-settings'); ?>
	<form method="post">
	<?php
		wp_nonce_field('saveEventsCalendarOptions');
		$this->do_action('tribe-events-settings-top');
		$this->do_action('tribe-events-'.$tab.'-settings-content');
		if (!has_action('tribe-events-'.$tab.'-settings-content')) {
			echo '<p>'.__('You\'ve requested a non-existent tab.', 'tribe-events-calendar').'</p>';
		}
		$this->do_action('tribe-events-after-'.$tab.'-settings');
 		$this->do_action( 'tribe_events_options_bottom' );
 	?>
  <?php if (has_action('tribe-events-'.$tab.'-settings-content')) : ?>
		<table>
			<tr>
		    	<td>
		    		<input id="saveEventsCalendarOptions" class="button-primary" type="submit" name="saveEventsCalendarOptions" value="<?php _e('Save Changes', 'tribe-events-calendar'); ?>" />
		        </td>
		    </tr>	
		 </table>
	<?php endif; ?>
</form>
<?php $this->do_action( 'tribe_events_options_post_form' ); ?>
</div>
</div>
