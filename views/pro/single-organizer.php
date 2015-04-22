<?php
/**
 * Single Organizer Template
 * The template for an organizer. By default it displays organizer information and lists 
 * events that occur with the specified organizer.
 *
 * This view contains the filters required to create an effective single organizer view.
 *
 * You can recreate an ENTIRELY new single organizer view by doing a template override, and placing
 * a single-organizer.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/single-organizer.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendarPro
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$organizer_id = get_the_ID();

?>

<?php while( have_posts() ) : the_post(); ?>
<div class="tribe-events-organizer">
		<p class="tribe-events-back">
			<a href="<?php echo esc_url( tribe_get_events_link() ); ?>" rel="bookmark"><?php _e( '&larr; Back to Events', 'tribe-events-calendar-pro' ) ?></a>
		</p>

	<?php do_action( 'tribe_events_single_organizer_before_organizer' ) ?>
	<div class="tribe-events-organizer-meta vcard tribe-clearfix">

			<!-- Organizer Title -->
			<?php do_action('tribe_events_single_organizer_before_title') ?>
			<?php the_title('<h2 class="entry-title author fn org">','</h2>'); ?>
			<?php do_action('tribe_events_single_organizer_after_title') ?>

			<!-- Organizer Meta -->
			<?php do_action( 'tribe_events_single_organizer_before_the_meta'); ?>
			<?php echo tribe_get_meta_group( 'tribe_event_organizer' ) ?>
			<?php do_action( 'tribe_events_single_organizer_after_the_meta' ) ?>

			<!-- Organizer Featured Image -->
			<?php echo tribe_event_featured_image(null, 'full') ?>

			<!-- Organizer Content -->
			<?php if( get_the_content() ) { ?>
			<div class="tribe-organizer-description tribe-events-content entry-content">
				<?php the_content(); ?>
			</div>
			<?php } ?>

		</div>
		<!-- .tribe-events-organizer-meta -->
	<?php do_action( 'tribe_events_single_organizer_after_organizer' ) ?>

	<!-- Upcoming event list -->
	<?php do_action('tribe_events_single_organizer_before_upcoming_events') ?>
	<?php // Use the 'tribe_events_single_organizer_posts_per_page' to filter the 
	 	  // number of events to display beneath the venue info on the venue page.
	?> 
		<?php echo tribe_include_view_list( array( 'organizer'    => get_the_ID(),
		                                           'eventDisplay' => 'list',
				apply_filters( 'tribe_events_single_organizer_posts_per_page', 100 )
			) ) ?>
	<?php do_action('tribe_events_single_organizer_after_upcoming_events') ?>
	
</div><!-- .tribe-events-organizer -->
<?php do_action( 'tribe_events_single_organizer_after_template' ) ?>
<?php endwhile; ?>
