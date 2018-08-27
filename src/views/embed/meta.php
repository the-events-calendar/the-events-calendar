<?php
/**
 * Embed Meta Template
 *
 * The meta template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/meta.php
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$event_id = get_the_ID();

// Setup an array of venue details for use later in the template
$venue = tribe_get_venue_single_line_address( $event_id );

do_action( 'tribe_events_embed_before_the_event_meta' );
?>
<div class="tribe-events-event-meta">
	<!-- Event Cost -->
	<?php do_action( 'tribe_events_embed_before_the_event_cost' ) ?>
	<?php tribe_get_template_part( 'embed/cost' ); ?>
	<?php do_action( 'tribe_events_embed_after_the_event_cost' ) ?>

	<!-- Schedule & Recurrence Details -->
	<?php do_action( 'tribe_events_embed_before_the_event_schedule_details' ) ?>
	<?php tribe_get_template_part( 'embed/schedule' ); ?>
	<?php do_action( 'tribe_events_embed_after_the_event_schedule_details' ) ?>

	<!-- Venue Details -->
	<?php do_action( 'tribe_events_embed_before_the_event_venue' ) ?>
	<?php tribe_get_template_part( 'embed/venue' ); ?>
	<?php do_action( 'tribe_events_embed_after_the_event_venue' ) ?>
</div>
<?php
do_action( 'tribe_events_embed_after_the_event_meta' );
