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


<?php /** todo: above should be moved **/ ?>