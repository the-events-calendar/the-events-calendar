<?php
/**
 * Block: Event Venue
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/event-venue.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7
 *
 */

$event_id = $this->get( 'post_id' );

$map = tribe_get_embedded_map() ? 'tribe-block__venue--has-map' : '';
?>
<div class="tribe-block tribe-block__venue <?php echo esc_attr( $map ); ?>">
	<?php do_action( 'tribe_events_single_event_meta_secondary_section_start' ); ?>

	<?php $this->template( 'blocks/parts/venue' ); ?>
	<?php $this->template( 'blocks/parts/map' ); ?>

	<?php do_action( 'tribe_events_single_event_meta_secondary_section_end' ); ?>
</div>
