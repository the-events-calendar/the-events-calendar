<?php

$settings = TribeSettings::instance();
$tabs = array();
$i = 0;
foreach ( $settings->tabs as $slug => $name ) {
	$tabs[$i]['slug'] = $slug;
	$tabs[$i]['name'] = $name;
	$i++;
}

?>
<div class="tribe_settings wrap">
<h2><?php printf( __('%s Network Settings', 'tribe-events-calendar'), $this->pluginName ); ?></h2>
<div id="tribe-events-options-error" class="tribe-events-error error"></div>
<?php $this->do_action( 'tribe_events_network_options_top' ); ?>
<div class="form">
	<form method="post">
	<?php
	wp_nonce_field('saveEventsCalendarNetworkOptions'); 
	?>
	<h3><?php _e('Settings', 'tribe-events-calendar'); ?></h3>
	<p><?php _e('Which tabs would you like displayed in the site settings pages?', 'tribe-events-calendar'); ?></p>
	<table class="form-table">
	<?php foreach( $tabs as $tab ) { ?>
		<tr>
			<th scope="row"><?php echo $tab['name']; ?></th>
			<td><fieldset>
				<legend class="screen-reader-text">
	                    <span><?php echo $tab['name']; ?></span>
	             </legend>
	             <label title="<?php echo $tab['name']; ?>">
					<input type="checkbox" id="showTabs[<?php echo $tab['slug']; ?>]" name="showTabs[<?php echo $tab['slug']; ?>]" value="1" <?php checked( array_key_exists( $tab['slug'], tribe_get_network_option('showTabs', array( $tab['slug'] => 1 ) ) ) ); ?>/>
	            </label>		
	        </fieldset></td>
		</tr>
	<?php } ?>
	</table>
	<table>
		<tr>
	    	<td>
	    		<input id="saveEventsCalendarNetworkOptions" class="button-primary" type="submit" name="saveEventsCalendarNetworkOptions" value="<?php _e('Save Changes', 'tribe-events-calendar'); ?>" />
	        </td>
	    </tr>
	 </table>
</form>

<?php $this->do_action( 'tribe_events_network_options_post_form' ); ?>
</div>
</div>