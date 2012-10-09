<div id="tribe-geo-wrapper">

	<div id="tribe-geo-data">
		<label for="location" class="tribe-events-visuallyhidden"><?php _e( 'Show events close to', 'tribe-events-calendar-pro' );?></label>
		<input type="text" id="tribe-geo-location" name="location" placeholder="<?php _e( 'Show events close to', 'tribe-events-calendar-pro' );?>" value="" size="40"/>

		<input type="button" id="tribe-geo-search" name="search" class="tribe-events-button-grey tribe-active" value="<?php _e( 'Search', 'tribe-events-calendar-pro' ); ?>"/>

	</div>
        <div id="tribe-geo-map-wrapper">
		<div id="tribe-geo-loading"><span></span></div>
		<div id="tribe-geo-map"></div>
	</div>	

	<div id="tribe-geo-options">
		<h2><?php _e( 'Refine your search:', 'tribe-events-calendar-pro' );?></h2>

		<div id="tribe-geo-links"></div>
	</div>

	<div id="tribe-geo-results"></div>

</div>
