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
 * @version TBD
 *
 * @var $index
 * @var $width
 * @var $height
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}


// @todo: Send this from the class calling it...
$api_key = tribe_get_option( Tribe__Events__Google__Maps_API_Key::$api_key_option_name, Tribe__Events__Google__Maps_API_Key::$default_api_key );

?>

<iframe
  width="<?php echo $width; ?>"
  height="<?php echo $height; ?>"
  frameborder="0" style="border:0"
  src="https://www.google.com/maps/embed/v1/place?key=<?php echo $api_key; ?>
    &q=Space+Needle,Seattle+WA" allowfullscreen>
</iframe>