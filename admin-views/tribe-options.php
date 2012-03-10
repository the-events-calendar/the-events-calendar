<?php

/**
 * This generates the HTML for the settings page
 * @since 2.0.5
 * @author jkudish
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>

<?php /** todo: below should be moved **/ ?>

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

<?php /** todo: above should be moved **/ ?>


<div class="tribe_settings wrap">
	<?php screen_icon(); ?><h2><?php printf( _x('%s Settings', 'The Event Calendar settings heading', 'tribe-events-calendar'), $this->menuName ); ?></h2>
	<?php $this->generateTabs( $this->currentTab ); ?>
		<?php do_action( 'tribe_settings_below_tabs' ); ?>
		<?php do_action( 'tribe_settings_below_tabs_tab_'.$this->currentTab ); ?>
		<div class="form">
			<?php do_action( 'tribe_settings_above_form_element' ); ?>
			<?php do_action( 'tribe_settings_above_form_element_tab_'.$this->currentTab ); ?>
			<?php echo apply_filters( 'tribe_settings_form_element', '<form method="post">' ); ?>
				<?php
					wp_nonce_field('saveTribeOptions');
					do_action( 'tribe_settings_before_content' );
					do_action( 'tribe_settings_before_content_tab_'.$this->currentTab );
					do_action( 'tribe_settings_content_tab_'.$this->currentTab );
					if ( !has_action( 'tribe_settings_content_tab_'.$this->currentTab ) )
						echo '<p>'.__('You\'ve requested a non-existent tab.', 'tribe-events-calendar').'</p>';
					do_action( 'tribe_settings_after_content_tab_'.$this->currentTab );
		 			do_action( 'tribe_settings_after_content' );
		 		?>
		  	<?php if (has_action('tribe_settings_content_tab_'.$this->currentTab) && !in_array($this->currentTab, $this->noSaveTabs) ) : ?>
	    		<input type="hidden" name="current-settings-tab" id="current-settings-tab" value="<?php echo $this->currentTab; ?>" />
	    		<input id="saveTribeOptions" class="button-primary" type="submit" name="saveTribeOptions" value="<?php _e('Save Changes', 'tribe-events-calendar'); ?>" />
				<?php endif; ?>
			</form>
			<?php do_action( 'tribe_settings_after_form_element' ); ?>
	</div>
</div>