	<h3><?php _e('License Key', 'tribe-events-calendar-pro'); ?></h3>
	<p><?php _e('A valid license key is required for support and updates.', 'tribe-events-calendar-pro') ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('License Key','tribe-events-calendar-pro'); ?></th>
	        <td>
	            <fieldset>
	                <legend class="screen-reader-text">
	                    <span><?php _e('A valid license key is required for support and updates.','tribe-events-calendar-pro'); ?></span>
	                </legend>
	                <label title='Replace empty fields'>
	                    <input type="text" name="licenseKey" value="<?php echo $licenseKey ?>" />
	                </label>
                   <a href='#validate-key123' id='validate-key'>Validate</a>
                   <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" id="ajax-loading-license" alt="" style='display: none'/>
                   <span id='valid-key' style='display:none;color:green'>Valid Key - Expires on <span id='expire-date'></span></span><span id='invalid-key' style='display:none;color:red'>Invalid Key</span>
	            </fieldset>
	        </td>
		</tr>
	</table>
   <script>
      jQuery(document).ready(function($) {
         $('#validate-key').click(function() {
            $('#invalid-key').hide();
            $('#valid-key').hide();
            $('#ajax-loading-license').show();

            var data = { action: 'tribe-validate-key', key: $('[name="licenseKey"]').val() };
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

