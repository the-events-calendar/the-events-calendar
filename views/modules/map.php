<?php
/**
 * Template used for maps embedded within single events and venues.
 * Override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe-events/modules/map.php
 *
 * @var $width
 * @var $height
 */

defined( 'ABSPATH' ) or exit( '-1' );
?>
<div id="tribe-events-gmap" style="height: <?php echo is_numeric( $height ) ? "{$height}px" : $height ?>; width: <?php echo is_numeric( $width ) ? "{$width}px" : $width ?>; margin-bottom: 15px;"></div><!-- #tribe-events-gmap -->
