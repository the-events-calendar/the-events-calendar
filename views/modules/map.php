<?php
/**
 * Template used for maps embedded within single events and venues.
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe-events/modules/map.php
 *
 * @var $index
 * @var $width
 * @var $height
 */

defined( 'ABSPATH' ) or exit( '-1' );

$style = "height: $height; width: $width; margin-bottom: 15px";
?>
<div id="tribe-events-gmap-<?php esc_attr_e( $index ) ?>" style="<?php esc_attr_e( $style ) ?>"></div><!-- #tribe-events-gmap-<?php esc_attr_e( $index ) ?> -->
