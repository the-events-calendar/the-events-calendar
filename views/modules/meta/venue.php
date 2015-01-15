<?php
/**
 * Single Event Meta (Venue) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/venue.php
 *
 * @package TribeEventsCalendar
 */

if ( ! tribe_get_venue_id() ) {
	return;
}

$phone   = tribe_get_phone();
$website = tribe_get_venue_website_link();

$address = tribe_address_exists() ? '<address class="tribe-events-address">' . tribe_get_full_address() . '</address>' : '';

$gmap_link = tribe_get_venue_address_gmap_link();

?>

<div class="tribe-events-meta-group tribe-events-meta-group-venue">
	<h3 class="tribe-events-single-section-title"> <?php _e( tribe_get_venue_label_singular(), 'tribe-events-calendar' ) ?> </h3>
	<dl>
		<?php do_action( 'tribe_events_single_meta_venue_section_start' ) ?>

		<dd class="author fn org"> <?php echo tribe_get_venue() ?> </dd>

		<?php
		// Display the address if it exists and a google maps link if it exists.
		if ( ! empty( $address ) ) {
			echo '<dd class="location">' . "$address $gmap_link </dd>";
		}
		?>

		<?php if ( ! empty( $phone ) ): ?>
			<dt> <?php _e( 'Phone:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="tel"> <?php echo $phone ?> </dd>
		<?php endif ?>

		<?php if ( ! empty( $website ) ): ?>
			<dt> <?php _e( 'Website:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="url"> <?php echo $website ?> </dd>
		<?php endif ?>

		<?php do_action( 'tribe_events_single_meta_venue_section_end' ) ?>
	</dl>
</div>