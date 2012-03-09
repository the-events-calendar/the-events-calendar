<?php
/**
* Settings panel
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>

<script type="text/javascript">
jQuery(document).ready(function($) {
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
<?php screen_icon(); ?><h2><?php printf( __('%s Settings', 'tribe-events-calendar'), $this->menuName ); ?></h2>
<div id="tribe-events-options-error" class="tribe-events-error error"></div>
<?php $this->generateTabs( $this->currentTab );
do_action( 'tribe_events_options_top' ); ?>
<div class="form">
<?php do_action('tribe-events-before-'.$this->currentTab.'-settings'); ?>
	<form method="post">
	<?php
		wp_nonce_field('saveTribeOptions');
		do_action('tribe-events-settings-top');
		do_action('tribe-events-'.$this->currentTab.'-settings-content');
		if (!has_action('tribe-events-'.$this->currentTab.'-settings-content')) {
			echo '<p>'.__('You\'ve requested a non-existent tab.', 'tribe-events-calendar').'</p>';
		}
		do_action('tribe-events-after-'.$this->currentTab.'-settings');
 		do_action( 'tribe_events_options_bottom' );
 	?>
  <?php if (has_action('tribe-events-'.$this->currentTab.'-settings-content') && $this->currentTab != 'help') : ?>
		<table>
			<tr>
		    	<td>
		    		<input type="hidden" name="current-settings-tab" id="current-settings-tab" value="<?php echo $this->currentTab; ?>" />
		    		<input id="saveTribeOptions" class="button-primary" type="submit" name="saveTribeOptions" value="<?php _e('Save Changes', 'tribe-events-calendar'); ?>" />
		        </td>
		    </tr>	
		 </table>
	<?php endif; ?>
</form>
<?php do_action( 'tribe_events_options_post_form' ); ?>
</div>
</div>
