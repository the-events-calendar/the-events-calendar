<?php 
/**
 * List Footer Nav Template
 * This file loads the list view navigation.
 *
 * You can recreate an ENTIRELY new list header nav by doing a template override, and placing
 * a list/nav-header.php file in a tribe-events/ directory within your theme directory, which
 * will override /views/list/nav-header.php. 
 * *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */
?>

<?php do_action( 'tribe_events_list_before_footer_nav' ); ?>
<h3 class="tribe-events-visuallyhidden"><?php _e( 'Events List Navigation', 'tribe-events-calendar' ) ?></h3>
<ul class="tribe-events-sub-nav">

	<!-- Left Navigation -->
	<?php if( tribe_is_past() ) : ?>
		<li class="tribe-events-nav-next tribe-events-nav-left tribe-events-past">
		<?php if( get_next_posts_link() ) : ?>
			<a href="<?php echo tribe_get_past_link() ?>"><?php _e( '&laquo; Previous Events', 'tribe-events-calendar' ) ?></a>
		<?php endif; ?>
		</li><!-- .tribe-events-nav-previous -->
	<?php elseif ( tribe_is_upcoming() ) : ?>
		<?php if( get_previous_posts_link() ) : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-left">
			<?php echo get_previous_posts_link( __( '&laquo; Previous Events', 'tribe-events-calendar' ) ) ?>
		<?php else : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-left tribe-events-past">
				<a href="<?php echo tribe_get_past_link() ?>" rel="prev"><?php _e( '&laquo; Previous Events', 'tribe-events-calendar' ) ?></a>
		<?php endif; ?>
		</li><!-- .tribe-events-nav-previous -->
	<?php endif; ?>

	<!-- Right Navigation -->
	<?php if( tribe_is_past() ) : ?>
		<?php if( get_previous_posts_link() ) : ?>
			<li class="tribe-events-nav-previous tribe-events-nav-right tribe-events-past">
			<?php echo get_previous_posts_link( __( 'Next Events &raquo;', 'tribe-events-calendar' ) ); ?>
		<?php else : ?>
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
<?php do_action( 'tribe_events_list_after_footer_nav' ); ?>
