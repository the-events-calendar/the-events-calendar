<?php
/**
 * PUE License Admin
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$expiration = $this->get_key_expiration();
$expiration_date = $expiration['expiration'];

?>
<h3><?php _e('License Key', 'plugin-update-engine'); ?></h3>
<p><?php 
	_e('A valid license key is required for support and updates.', 'plugin-update-engine'); 
	if ($expiration['expired']) { // expired
		echo ' <span class="expiring-license">'.$this->expiredNotice.'</span>';
	} elseif (is_string($expiration_date) && $expiration_date != '' && (strtotime($expiration_date) < time()+(604800))) { // expires within the week
		echo ' <span class="expiring-license">'.$this->expiringWeekNotice.'</span>';
	}
	?></p>
<table class="form-table">
	<tr>
		<th scope="row"><?php _e('License Key','plugin-update-engine'); ?></th>
		<td>
			<fieldset>
			<legend class="screen-reader-text">
				<span><?php _e('A valid license key is required for support and updates.','plugin-update-engine'); ?></span>
			</legend>
			<label title='Replace empty fields'>
				<input type="text" name="install_key" id="install_key" value="<?php echo $this->install_key ?>" size="45" />
			</label>
			<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="ajax-loading-license" alt="" style='display: none'/>
			<span id='valid-key' style='display:none;color:green'></span>
			<span id='invalid-key' style='display:none;color:red'></span>
			</fieldset>
		</td>
	</tr>
</table>
<script>
jQuery(document).ready(function($) {
	$('#install_key').change(function() { validateKey(); });
	validateKey();
});
function validateKey() {
	if (jQuery('[name="install_key"]').val() != '') {
		jQuery('#invalid-key').hide();
		jQuery('#valid-key').hide();
		jQuery('#ajax-loading-license').show();
		
		//strip whitespace from key
		var license_key = jQuery('[name="install_key"]').val().replace(/^\s+|\s+$/g, "");
		jQuery('[name="install_key"]').val( license_key );

		var data = { action: 'pue-validate-key_<?php echo $this->slug; ?>', key: license_key };
		jQuery.post(ajaxurl, data, function(response) {
			var data = jQuery.parseJSON(response);
			jQuery('#ajax-loading-license').hide();
			if(data.status == '1') {
				jQuery('#valid-key').show();
				jQuery('#valid-key').text(data.message);
				jQuery('#invalid-key').hide();
			} else {
				jQuery('#invalid-key').show();
				jQuery('#invalid-key').text(data.message);
				jQuery('#valid-key').hide();
			}
		});
	}
}
</script>