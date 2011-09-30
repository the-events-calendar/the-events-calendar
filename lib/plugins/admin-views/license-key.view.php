<h3><?php _e('License Key', 'plugin-update-engine'); ?></h3>
<p><?php _e('A valid license key is required for support and updates.', 'plugin-update-engine') ?></p>
<table class="form-table">
	<tr>
		<th scope="row"><?php _e('License Key','plugin-update-engine'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e('A valid license key is required for support and updates.','plugin-update-engine'); ?></span>
                </legend>
                <label title='Replace empty fields'>
                    <input type="text" name="install_key" id="install_key" value="<?php echo $this->install_key ?>" />
                </label>
               <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="ajax-loading-license" alt="" style='display: none'/>
               <span id='valid-key' style='display:none;color:green'><?php _e('Valid Key! Expires on ','plugin-update-engine'); ?><span id='expire-date'></span></span><span id='invalid-key' style='display:none;color:red'><?php _e('Sorry, this key is not valid.','plugin-update-engine'); ?></span>
            </fieldset>
        </td>
	</tr>
</table>
<script>
   jQuery(document).ready(function($) {
      $('#install_key').change(function() {
         $('#invalid-key').hide();
         $('#valid-key').hide();
         $('#ajax-loading-license').show();

         var data = { action: 'pue-validate-key_<?php echo $this->slug; ?>', key: $('[name="install_key"]').val() };
         jQuery.post(ajaxurl, data, function(response) {
            $('#ajax-loading-license').hide();

            if(response == "0") {
               $('#invalid-key').show();
               $('#valid-key').hide();
            } else {
               $('#expire-date').text(response);
               $('#invalid-key').hide();
               $('#valid-key').show();
            }
         });
      });
   });
</script>