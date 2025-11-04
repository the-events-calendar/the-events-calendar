<?php
/**
 * Single Event Meta (Venue) Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe-events/modules/meta/venue.php
 *
 * @version 6.15.11
 *
 * @since 4.6.19
 * @since 6.15.3 Added post password protection.
 * @since 6.15.11 Replaced definition list markup with unordered list and removed empty dt tags for improved accessibility.
 *
 * @package TribeEventsCalendar
 */

if ( ! tribe_get_venue_id() ) {
	return;
}

$phone   = tribe_get_phone();
$website = tribe_get_venue_website_link();
$website_title = tribe_events_get_venue_website_title();

?>

<div class="tribe-events-meta-group tribe-events-meta-group-venue">
	<h2 class="tribe-events-single-section-title"> <?php echo esc_html( tribe_get_venue_label_singular() ) ?> </h2>
	<ul class="tribe-events-meta-list">
		<?php do_action( 'tribe_events_single_meta_venue_section_start' ) ?>
		<li class="tribe-events-meta-item tribe-venue"> <?php echo wp_kses_post( tribe_get_venue() ); ?> </li>

		<?php if ( ! post_password_required( tribe_get_venue_id() ) ) : ?>
			<?php if ( tribe_address_exists() ) : ?>
				<li class="tribe-events-meta-item tribe-venue-location">
					<address class="tribe-events-address">
						<?php echo tribe_get_full_address(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>

						<?php if ( tribe_show_google_map_link() ) : ?>
							<?php echo tribe_get_map_link_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</address>
				</li>
			<?php endif; ?>

			<?php if ( ! empty( $phone ) ) : ?>
				<li class="tribe-events-meta-item">
					<span class="tribe-venue-tel-label tribe-events-meta-label"><?php esc_html_e( 'Phone', 'the-events-calendar' ); ?></span>
					<span class="tribe-venue-tel tribe-events-meta-value"> <?php echo esc_html( $phone ); ?> </span>
				</li>
			<?php endif; ?>

			<?php if ( ! empty( $website ) ) : ?>
				<li class="tribe-events-meta-item">
					<?php if ( ! empty( $website_title ) ) : ?>
						<span class="tribe-venue-url-label tribe-events-meta-label"><?php echo esc_html( $website_title ); ?></span>
					<?php endif; ?>
					<span class="tribe-venue-url tribe-events-meta-value"> <?php echo $website; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?> </span>
				</li>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'tribe_events_single_meta_venue_section_end' ) ?>
	</ul>
</div>
