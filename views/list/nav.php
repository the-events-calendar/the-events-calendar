<?php 
/**
 * List Nav Template
 * This file loads the list view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/calendar/nav.php 
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<h3 class="tribe-events-visuallyhidden">'. __( 'Events List Navigation', 'tribe-events-calendar' )</h3>
<ul class="tribe-events-sub-nav">
	<!-- Left Navigation -->
	<?php if( tribe_is_past() ) : ?>
		<li class="tribe-events-nav-next tribe-events-nav-left tribe-events-past">
		<?php if( get_next_posts_link() ) : ?>
			<a href="<?php tribe_get_past_link() ?>"><?php _e( '&laquo; Previous Events', 'tribe-events-calendar' ) ?></a>
		<?php endif; ?>
		</li><!-- .tribe-events-nav-previous -->
	<?php elseif ( tribe_is_upcoming() ) : ?>
		<?php if( get_previous_posts_link() ) : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-left">
			<a href="<?php echo tribe_get_upcoming_link() ?>" rel="pref"><?php _e( '&laquo; Previous Events', 'tribe-events-calendar' ) ?></a>
		<?php else : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-left tribe-events-past">
			<a href="<?php echo tribe_get_past_link() ?>" rel="pref"><?php _e( '&laquo; Previous Events', 'tribe-events-calendar' ) ?></a>
		<?php endif; ?>
		</li><!-- .tribe-events-nav-previous -->
	<?php endif; ?>

	<!-- Right Navigation -->
	<?php if( tribe_is_past() ) : ?>
		<?php if( get_query_var( 'paged' ) > 1 ) : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-right tribe-events-past">
				<a href="<?php echo tribe_get_past_link() ?>" rel="pref"><?php _e( 'Next Events &raquo;', 'tribe-events-calendar' ) ?></a>
		<?php elseif( !get_previous_posts_link() ) : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-right">
				<a href="<?php echo tribe_get_upcoming_link() ?>" rel="next"><?php _e( 'Next Events &raquo;', 'tribe-events-calendar' ) ?></a>
		<?php endif; ?>
		</li><!-- .tribe-events-nav-previous -->
	<?php elseif ( tribe_is_upcoming() ) : ?>
		<li class="tribe-events-nav-next tribe-events-nav-right">
		<?php if( get_next_posts_link() ) : ?> 
			<a href="<?php echo tribe_get_upcoming_link() ?>" rel="next"><?php _e( 'Next Events &raquo;', 'tribe-events-calendar' ) ?></a>
		<?php endif; ?>
		</li><!-- .tribe-events-nav-previous -->
	<?php endif; ?>
</ul>