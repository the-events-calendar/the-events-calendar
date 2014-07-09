<?php
/**
 * Events Pro List Widget Template
 * This is the template for the output of the events list widget.
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/list-widget.php
 *
 * When the template is loaded, the following vars are set: $start, $end, $venue,
 * $address, $city, $state, $province'], $zip, $country, $phone, $cost
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( !defined('ABSPATH') ) die('-1');

// Have taxonomy filters been applied?
$filters = json_decode( $filters, true );

// Is the filter restricted to a single taxonomy?
$single_taxonomy = ( is_array( $filters ) && 1 === count( $filters ) );
$single_term = false;

// Pull the actual taxonomy and list of terms into scope
if ( $single_taxonomy ) foreach ( $filters as $taxonomy => $terms );

// If we have a single taxonomy and a single term, the View All link should point to the relevant archive page
if ( $single_taxonomy && 1 === count( $terms ) ) {
	$link_to_archive = true;
	$link_to_all = get_term_link( absint( $terms[0] ), $taxonomy );
}

// Otherwise link to the main events page
else {
	$link_to_archive = false;
	$link_to_all = tribe_get_events_link();
}

// Check if any posts were found
if ( $posts ):
	?>
	<ol class="hfeed vcalendar">
		<?php
		foreach( $posts as $post ) :
			setup_postdata( $post );
			?>
			<li class="<?php tribe_events_event_classes() ?>">

				<?php do_action( 'tribe_events_list_widget_before_the_event_title' ); ?>

				<h4 class="entry-title summary">
					<a href="<?php echo tribe_get_event_link(); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h4>

				<?php do_action( 'tribe_events_list_widget_after_the_event_title' ); ?>

				<?php do_action( 'tribe_events_list_widget_before_the_meta' ) ?>

				<div class="duration">
					<?php echo tribe_events_event_schedule_details(); ?>
				</div>
				<?php if ( $cost && tribe_get_cost() != '' ) { ?>
					<span class="tribe-events-divider">|</span>
					<div class="tribe-events-event-cost">
						<?php echo tribe_get_cost( null, true ); ?>
					</div>
				<?php } ?>
				<div class="vcard adr location">

					<?php if ( $venue  && tribe_get_venue() != '') { ?>
						<span class="fn org tribe-venue"><?php echo tribe_get_venue_link(); ?></span>
					<?php } ?>

					<?php if ( $address && tribe_get_address() != '' ) { ?>
						<span class="street-address"><?php echo tribe_get_address(); ?></span>
					<?php } ?>

					<?php if ( $city && tribe_get_city() != '' ) { ?>
						<span class="locality"><?php echo tribe_get_city(); ?></span>
					<?php } ?>

					<?php if ( $region && tribe_get_region() !='' ) { ?>
						<span class="region"><?php echo tribe_get_region(); ?></span>
					<?php	} ?>

					<?php if ( $zip && tribe_get_zip() != '' ) { ?>
						<span class="postal-code"><?php echo tribe_get_zip(); ?></span>
					<?php } ?>

					<?php if ( $country  && tribe_get_country() != '') { ?>
						<span class="country-name"><?php echo tribe_get_country(); ?></span>
					<?php } ?>

					<?php if ( $organizer && tribe_get_organizer() != '' ) { ?>
						<?php _e( 'Organizer:', 'tribe-events-calendar-pro' ); ?>
						<span class="tribe-organizer"><?php echo tribe_get_organizer_link(); ?></span>
					<?php } ?>

					<?php if ( $phone && tribe_get_phone() != '' ) { ?>
						<span class="tel"><?php echo tribe_get_phone(); ?></span>
					<?php } ?>
				</div>

				<?php do_action( 'tribe_events_list_widget_after_the_meta' ) ?>

			</li>
		<?php
		endforeach;
		?>
	</ol><!-- .hfeed -->

	<p class="tribe-events-widget-link">
		<a href="<?php esc_attr_e( esc_url( $link_to_all ) ) ?>" rel="bookmark">
			<?php _e( 'View More&hellip;', 'tribe-events-calendar-pro' ) ?>
		</a>
	</p>
<?php
// No Events were Found
else:
	?>
	<p><?php _e( 'There are no upcoming events at this time.', 'tribe-events-calendar' ) ?></p>
<?php
endif;