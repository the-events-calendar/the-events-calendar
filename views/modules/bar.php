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
	
	<form id="tribe-bar-form" name="tribe-bar-form" method="post" action="<?php echo add_query_arg( array() ); ?>">
	
		<div id="tribe-bar-filters">
			<?php self::print_filters_helper( $filters ); ?>
		</div><!-- #tribe-bar-filters -->
		
		<div id="tribe-bar-views">
			<?php self::print_views_helper( $views ); ?>
		</div><!-- #tribe-bar-filters -->
		
		<div class="tribe-clear"></div>
		
	</form><!-- #tribe-bar-form -->
	
</div><!-- #tribe-events-bar -->