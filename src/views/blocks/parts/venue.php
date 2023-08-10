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
 * @version 5.0.1
 *
 */

if ( ! tribe_get_venue_id() ) {
	return;
}
$attributes = $this->get( 'attributes', [] );

$phone   = tribe_get_phone();
$website = tribe_get_venue_website_link();

?>

<div class="tribe-block__venue__meta">
	<div class="tribe-block__venue__name">
		<h3><?php echo tribe_get_venue_link() ?></h3>
	</div>

	<?php do_action( 'tribe_events_single_meta_venue_section_start' ) ?>

	<?php if ( tribe_address_exists() ) : ?>
		<address class="tribe-block__venue__address">
			<?php echo tribe_get_full_address(); ?>

			<?php if ( tribe_show_google_map_link() ) : ?>
				<?php echo tribe_get_map_link_html(); ?>
			<?php endif; ?>
		</address>
	<?php endif; ?>

	<?php if ( ! empty( $phone ) ) : ?>
		<span class="tribe-block__venue__phone"><?php echo $phone ?></span><br />
	<?php endif ?>

	<?php if ( ! empty( $website ) ) : ?>
		<span class="tribe-block__venue__website"><?php echo $website ?></span><br />
	<?php endif ?>

	<?php do_action( 'tribe_events_single_meta_venue_section_end' ) ?>
</div>
