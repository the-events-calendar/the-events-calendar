<?php
/**
 * Template used for maps embedded within single events and venues when the site is using The Events Calendar's
 * default API Key--which means only Google's basic Map Embeds are available for use.
 *
 * See https://developers.google.com/maps/documentation/embed/usage-and-billing#embed for more info.
 *
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe-events/modules/map-basic.php
 *
 * @version 4.6.24
 *
 * @var string $embed_url The full embed URL.
 * @var string $address The venue's address as entered by the user.
 * @var int $index The array key associated with this map; will usually be 0 unless there's multiple maps on the page.
 * @var string|int $width The map's width in percent or pixels; defaults to '100%'.
 * @var string|int $height The map's height in percent or pixels; defaults to '350px'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>

<iframe
  aria-label="<?php esc_attr_e( 'Venue location map', 'the-events-calendar' ); ?>"
  width="<?php echo esc_attr( $width ); ?>"
  height="<?php echo esc_attr( $height ); ?>"
  frameborder="0" style="border:0"
  src="<?php echo esc_url( $embed_url ); ?>" allowfullscreen>
</iframe>