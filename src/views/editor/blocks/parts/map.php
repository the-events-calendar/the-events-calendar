<?php

if ( ! tribe_embed_google_map() ) {
	return;
}

$map = tribe_get_embedded_map();

if ( empty( $map ) ) {
	return;
}

?>

<div class="tribe-block__venue__map">
	<?php
	// Display the map.
	do_action( 'tribe_events_single_meta_map_section_start' );
	echo $map;
	do_action( 'tribe_events_single_meta_map_section_end' );
	?>
</div>
