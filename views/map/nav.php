<?php 
/**
 * Map Nav Template
 * This file contains the map view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/map/nav.php 
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php 

global $wp_query;

 ?>

<h3 class="tribe-events-visuallyhidden"><?php _e( 'Events List Navigation', 'tribe-events-calendar' ) ?></h3>
<ul class="tribe-events-sub-nav">
	<!-- Display Previous Page Navigation -->
	<li class="tribe-events-nav-previous"><a href="#" class="tribe_map_paged"><?php _e( '&laquo; Previous Events' ) ?></a></li>
	<!-- Display Next Page Navigation -->
	<li class="tribe-events-nav-next"
	<?php if ( $wp_query->max_num_pages === ( $wp_query->query_vars['paged'] ) ) : ?>
		 style="display:none;"
	<?php endif; ?>
	>
		<a href="#" class="tribe_map_paged"><?php _e( 'Next Events &raquo;' ) ?></a>
	</li><!-- .tribe-events-nav-next -->
</ul>