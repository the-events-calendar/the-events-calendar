<?php
/**
 * Events Pro List Widget Template
 * This is the template for the output of the events list widget.
 * All the items are turned on and off through the widget admin.
 * There is currently no default styling, which is highly needed.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/widgets/list-widget.php
 *
 * When the template is loaded, the following vars are set:
 *
 * @var string $start
 * @var string $end
 * @var string $venue
 * @var string $address
 * @var string $city
 * @var string $state
 * @var string $province
 * @var string $zip
 * @var string $country
 * @var string $phone
 * @var string $cost
 * @var array  $instance
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// We'll need to utilize the post global
global $post;

// Have taxonomy filters been applied?
$filters = json_decode( $filters, true );

// Is the filter restricted to a single taxonomy?
$single_taxonomy = ( is_array( $filters ) && 1 === count( $filters ) );
$single_term     = false;

// Pull the actual taxonomy and list of terms into scope
if ( $single_taxonomy ) foreach ( $filters as $taxonomy => $terms );

// If we have a single taxonomy and a single term, the View All link should point to the relevant archive page
if ( $single_taxonomy && 1 === count( $terms ) ) {
	$link_to_archive = true;
	$link_to_all     = get_term_link( absint( $terms[0] ), $taxonomy );
} // Otherwise link to the main events page
else {
	$link_to_archive = false;
	$link_to_all     = tribe_get_events_link();
}

// Check if any posts were found
if ( $posts ):
	foreach ( $posts as $post ) :
		setup_postdata( $post );
		do_action( 'tribe_events_widget_list_inside_before_loop' ); ?>

		<!-- Event  -->
		<div class="<?php tribe_events_event_classes() ?>">
			<?php tribe_get_template_part( 'pro/widgets/modules/single-event', null, $instance ) ?>
		</div><!-- .hentry .vevent -->

		<?php do_action( 'tribe_events_widget_list_inside_after_loop' ) ?>
	<?php endforeach ?>

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

// Cleanup
wp_reset_postdata();