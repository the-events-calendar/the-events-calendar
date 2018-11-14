<?php
/**
 * Renders the event Venue block
 *
 * @version 0.3.0-alpha
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
