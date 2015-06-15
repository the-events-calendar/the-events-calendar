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

?>

<div class="tribe-events-meta-group tribe-events-meta-group-venue">
	<h3 class="tribe-events-single-section-title"> <?php _e( tribe_get_venue_label_singular(), 'tribe-events-calendar' ) ?> </h3>
	<dl>
		<?php do_action( 'tribe_events_single_meta_venue_section_start' ) ?>

		<dd class="author fn org"> <?php echo tribe_get_venue() ?> </dd>

		<?php if ( tribe_address_exists() ) : ?>
			<dd class="location">
				<address class="tribe-events-address">
					<?php echo tribe_get_full_address(); ?>

					<?php if ( tribe_show_google_map_link() ) : ?>
						<?php echo tribe_get_map_link_html(); ?>
					<?php endif; ?>
				</address>
			</dd>
		<?php endif; ?>

		<?php if ( ! empty( $phone ) ): ?>
			<dt> <?php esc_html_e( 'Phone:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="tel"> <?php echo $phone ?> </dd>
		<?php endif ?>

		<?php if ( ! empty( $website ) ): ?>
			<dt> <?php esc_html_e( 'Website:', 'tribe-events-calendar' ) ?> </dt>
			<dd class="url"> <?php echo $website ?> </dd>
		<?php endif ?>

		<?php do_action( 'tribe_events_single_meta_venue_section_end' ) ?>
	</dl>
</div>
