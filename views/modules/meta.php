<?php
/**
 * Single Event Meta Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta.php
 *
 * @package TribeEventsCalendar
 * @since 3.6
 */

do_action( 'tribe_events_single_meta_before' );

// Check for skeleton mode (no outer wrappers per section)
$not_skeleton = ! apply_filters( 'tribe_events_single_event_the_meta_skeleton', false, get_the_ID() );

// Do we want to group venue meta separately?
$set_venue_apart = apply_filters( 'tribe_events_single_event_the_meta_group_venue', false, get_the_ID() );
?>

<?php
if ( $not_skeleton ) echo '<div class="tribe-events-single-section tribe-events-event-meta primary tribe-clearfix">';
?>

	<?php
	do_action( 'tribe_events_single_event_meta_primary_section_start' );

	// Always include the main event details in this first section
	tribe_get_template_part( 'modules/meta/details' );

	// If we have no map to embed and no need to keep the venue separate...
	if ( ! $set_venue_apart && ! tribe_embed_google_map() )
		tribe_get_template_part( 'modules/meta/venue' );

	// If we have no organizer, no need to separate the venue but we have a map to embed...
	elseif ( ! $set_venue_apart && ! tribe_has_organizer() && tribe_address_exists() && tribe_embed_google_map() ) {
		tribe_get_template_part( 'modules/meta/venue' );
		echo '<div class="tribe-events-meta-group tribe-events-meta-group-gmap">';
		tribe_get_template_part( 'modules/meta/map' );
		echo '</div>';
	}

	// If the venue meta has not already been displayed then it will be printed separately by default
	else $set_venue_apart = true;

	// Include organizer meta if appropriate
	if ( tribe_has_organizer() ) tribe_get_template_part( 'modules/meta/organizer' );

	do_action( 'tribe_events_single_event_meta_primary_section_end' );
	?>

<?php
if ( $not_skeleton ) echo '</div>';
?>

<?php if ( $set_venue_apart && tribe_address_exists() ): ?>
	<?php
	if ( $not_skeleton ) echo '<div class="tribe-events-single-section tribe-events-event-meta secondary tribe-clearfix">';
	?>
		<?php
		do_action( 'tribe_events_single_event_meta_secondary_section_start' );

		tribe_get_template_part( 'modules/meta/venue' );
		tribe_get_template_part( 'modules/meta/map' );

		do_action( 'tribe_events_single_event_meta_secondary_section_end' );
		?>
	<?php
	if ( $not_skeleton ) echo '</div>';
	?>
<?php
endif;

do_action( 'tribe_events_single_meta_after' );
?>