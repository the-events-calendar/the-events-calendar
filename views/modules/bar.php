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
	
	<form id="tribe-bar-form" class="tribe-clearfix" name="tribe-bar-form" method="post" action="<?php echo add_query_arg( array() ); ?>">
	
			<?php self::print_filters_helper( $filters ); ?>
		
		<div id="tribe-bar-views">
			<label for="tribe-bar-view"><?php echo __( 'View As', 'tribe-events-calendar' ); ?></label>
			<?php self::print_views_helper( $views ); ?>
		</div><!-- #tribe-bar-filters -->
		
	</form><!-- #tribe-bar-form -->
	
</div><!-- #tribe-events-bar -->
