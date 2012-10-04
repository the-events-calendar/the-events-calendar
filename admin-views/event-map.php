<?php
/**
* Inline Google map
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<div id="tribe-events-gmap" style="height: <?php echo is_numeric($height) ? "{$height}px" : $height ?>; width: <?php echo is_numeric($width) ? "{$width}px" : $width ?>; margin-bottom: 15px;"></div><!-- #tribe-events-gmap -->
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
var event_address;

function initialize() {
	var myOptions = {
	    zoom: <?php echo tribe_get_option( 'embedGoogleMapsZoom', '10' ); ?>,
	    center: event_address,
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	var map = new google.maps.Map(document.getElementById("tribe-events-gmap"), myOptions);
  
	var marker = new google.maps.Marker(
		{
			map: map,
			title: <?php echo json_encode(tribe_get_venue($postId)) ?>,
			position: event_address
		}
	);
}

function codeAddress(address) {
	var geocoder= new google.maps.Geocoder();
	var address = address || <?php echo json_encode($address) ?>;
	geocoder.geocode( 
		{ 'address': address }, 
		function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				event_address = results[0].geometry.location
				initialize();
			}
		}
	);
}

codeAddress();
</script>
