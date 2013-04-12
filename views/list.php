<?php
/**
 * Events List Template
 * The template for a list of events. This includes the Past Events and Upcoming Events views 
 * as well as those same views filtered to a specific category.
 *
 * This view contains the filters required to create an effective events list view.
 *
 * You can recreate an ENTIRELY new list view by doing a template override, and placing
 * a list.php file in a tribe-events/ directory within your theme directory, which
 * will override the /views/list.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

$the_post_id = ( have_posts() ) ? get_the_ID() : null; ?>


<?php do_action( 'tribe_events_list_before_template' ); ?>
<input type="hidden" id="tribe-events-list-hash" value="">

<div id="tribe-events-list-view">

	<div id="tribe-events-content" class="tribe-events-list">
	
		<!-- List Title -->
		<?php do_action( 'tribe_events_list_before_the_title' ); ?>
		<h2 class="tribe-events-page-title"><?php tribe_get_events_title() ?></h2>
		<?php do_action( 'tribe_events_list_after_the_title' ); ?>

		<!-- Notices -->
		<?php tribe_events_the_notices() ?>

		<?php if ( have_posts() ) : ?>
		
		<!-- List Header -->
	    <?php do_action( 'tribe_events_list_before_header' ); ?>
		<div id="tribe-events-header" <?php tribe_events_the_header_attributes() ?>>

			<!-- Header Navigation -->
			<?php tribe_get_template_part('list/nav', 'header'); ?>

		</div><!-- #tribe-events-header -->
		<?php do_action( 'tribe_events_list_after_header' ); ?>

		<!-- Start list loop -->
		<?php do_action( 'tribe_events_list_before_loop' ); ?>
		<div class="tribe-events-loop heed">
		
			<?php while ( have_posts() ) : the_post(); ?>

				<?php 

				global $more, $post; 
				$more = false;
				do_action('tribe_events_list_inside_before_loop');

				?>
				<?php //tribe_events_show_separators() ?>

				<!-- Event  -->
				<div id="post-<?php the_ID() ?>" class="<?php tribe_events_event_classes() ?>">

					<!-- Event Title -->
					<h2 class="tribe-events-list-event-title entry-title summary">
						<a class="url" href="<?php echo tribe_get_event_link() ?>" title="<?php the_title() ?>" rel="bookmark">
							<?php the_title() ?>
						</a>
					</h2>

					<!-- Event Meta -->
					<?php do_action( 'tribe_events_list_before_the_meta' ) ?>
					<div class="tribe-events-event-meta">

						<!-- Schedule & Recurrence Details -->
						<div class="updated published time-details">
							<?php echo tribe_events_event_schedule_details() ?>
							<?php echo tribe_events_event_recurring_info_tooltip() ?>
						</div>
						
						<!-- Venue Display Info -->
						<?php
						$venue_name = tribe_get_meta( 'tribe_event_venue_name' );
						$venue_address = tribe_get_meta( 'tribe_event_venue_address' );
						
						if( !empty( $venue_name ) && !empty( $venue_address ) )
							printf('<div class="tribe-events-venue-details">%s%s%s</div>',
								$venue_name,
								( !empty( $venue_name ) && !empty( $venue_address ) ) ? ', ' : '',
								( !empty( $venue_address ) ) ? $venue_address : ''
							);
						?>
					</div><!-- .tribe-events-event-meta -->
					<?php do_action( 'tribe_events_list_after_the_meta' ) ?>

					<!-- Event Image -->
					<?php tribe_event_featured_image(null, 'large') ?>

					<!-- Event Content -->
					<?php do_action( 'tribe_events_list_before_the_content' ) ?>
					<div class="tribe-events-list-event-description tribe-events-content entry-summary">
						<?php the_excerpt() ?>
						<a href="<?php echo tribe_get_event_link() ?>" class="tribe-events-read-more"><?php _e('Find out more', 'tribe-events-calendar') ?> &raquo;</a>
					</div><!-- .tribe-events-list-event-description -->
					<?php do_action('tribe_events_list_after_the_content') ?>

				</div><!-- .hentry .vevent -->
				<?php do_action( 'tribe_events_list_inside_after_loop' ); ?>

			<?php endwhile; ?>
			</div><!-- .tribe-events-loop -->
			<?php do_action('tribe_events_list_after_loop'); ?>

		<?php endif; ?>

		<!-- List Footer -->
		<?php do_action( 'tribe_events_list_before_footer' ); ?>
		<div id="tribe-events-footer">

			<!-- Footer Navigation -->
			<?php tribe_get_template_part('list/nav', 'footer') ?>

		</div><!-- #tribe-events-footer -->
		<?php do_action( 'tribe_events_list_after_footer' ) ?>

	</div><!-- #tribe-events-content -->

	<div class="tribe-clear"></div>

</div><!-- #tribe-events-list-view -->

<?php do_action('tribe_events_list_after_template') ?>
