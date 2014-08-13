<?php
/**
 * Embedded map template.
 *
 * This template is used to create the map embedded within single event and venue posts.
 * You can override this template in your own theme by creating a file at:
 *
 *     [your-theme]/tribe-events/pro/map/embedded.php
 *
 */

defined( 'ABSPATH' ) or exit();

$index  = isset( $index ) ? $index : 0;
$height = apply_filters( 'tribe_events_pro_embedded_map_height', '180px' );
$width  = apply_filters( 'tribe_events_pro_embedded_map_width', '100%' );
$style  = apply_filters( 'tribe_events_pro_embedded_map_style', "height: $height; width: $width" );
?>
<div class="tribe_events_pro_single_map" id="tribe_events_pro_single_map_<?php esc_attr_e( $index ) ?>" style="<?php esc_attr_e( $style ) ?>"></div>