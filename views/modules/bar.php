<?php
/**
 * Events Navigation Bar Module Template
 * Renders our events navigation bar used across our views
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<div id="tribe-events-bar">

	<div id="tribe-events-bar-filters">
		<?php self::print_filters_helper( $filters ); ?>
	</div><!-- #tribe-events-bar-filters -->
	<div id="tribe-events-bar-views">
		<?php self::print_views_helper( $views ); ?>
	</div><!-- #tribe-events-bar-filters -->
	
	<div class="tribe-clear"></div>
	
</div><!-- #tribe-events-bar -->