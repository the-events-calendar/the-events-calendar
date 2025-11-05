<?php
/**
 * Venue template part for the Event Venue block
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/venue.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.11
 *
 * @since 6.2.0 Be specific about which venue to render.
 * @since 6.15.11 Added proper escaping for phone output.
 *
 * @var bool $show_map_link Whether to show the map link or not.
 * @var ?int $venue_id The ID of the venue to display.
 */

if ( ! tribe_get_venue_id() ) {
	return;
}
$attributes = $this->get( 'attributes', [] );

$phone   = tribe_get_phone( $venue_id );
$website = tribe_get_venue_website_link( $venue_id );

?>

<div class="tribe-block__venue__meta">
	<div class="tribe-block__venue__name">
		<h3><?php echo tribe_get_venue_link( $venue_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?></h3>
	</div>

	<?php do_action( 'tribe_events_single_meta_venue_section_start' ) ?>

	<?php if ( ! post_password_required( $venue_id ) ) : ?>
		<?php if ( tribe_address_exists( $venue_id ) ) : ?>
			<address class="tribe-block__venue__address">
				<?php echo tribe_get_full_address( $venue_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>

				<?php if ( $show_map_link ) : ?>
					<?php echo tribe_get_map_link_html( $venue_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</address>
		<?php endif; ?>

		<?php if ( ! empty( $phone ) ) : ?>
			<span class="tribe-block__venue__phone"><?php echo esc_html( $phone ); ?></span><br />
		<?php endif; ?>

		<?php if ( ! empty( $website ) ) : ?>
			<span class="tribe-block__venue__website"><?php echo $website; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,StellarWP.XSS.EscapeOutput.OutputNotEscaped ?></span><br />
		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'tribe_events_single_meta_venue_section_end' ) ?>
</div>
