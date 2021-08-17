<?php
/**
 * Embed Featured Image Template
 *
 * The featured image template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/image.php
 *
 * @link http://evnt.is/1aiy
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$event_id = get_the_ID();

$thumbnail_id = 0;

if ( has_post_thumbnail() ) {
	$thumbnail_id = get_post_thumbnail_id();
}

if ( 'attachment' === get_post_type() && wp_attachment_is_image() ) {
	$thumbnail_id = get_the_ID();
}

if ( ! $thumbnail_id ) {
	return;
}

$aspect_ratio = 1;
$measurements = [ 1, 1 ];
$image_size   = 'full'; // Fallback.

$meta = wp_get_attachment_metadata( $thumbnail_id );
if ( is_array( $meta ) ) {
	foreach ( $meta['sizes'] as $size => $data ) {
		if ( $data['width'] / $data['height'] > $aspect_ratio ) {
			$aspect_ratio = $data['width'] / $data['height'];
			$measurements = [ $data['width'], $data['height'] ];
			$image_size   = $size;
		}
	}
}

/**
 * Filter the thumbnail image size for use in the embed template.
 *
 * @since 4.4.0
 * @since 4.5.0 Added `$thumbnail_id` parameter.
 *
 * @param string $image_size   Thumbnail image size.
 * @param int    $thumbnail_id Attachment ID.
 */
$image_size = apply_filters( 'embed_thumbnail_image_size', $image_size, $thumbnail_id );

$shape = $measurements[0] / $measurements[1] >= 1.75 ? 'rectangular' : 'square';

/**
 * Filter the thumbnail shape for use in the embed template.
 *
 * Rectangular images are shown above the title while square images
 * are shown next to the content.
 *
 * @since 4.4.0
 * @since 4.5.0 Added `$thumbnail_id` parameter.
 *
 * @param string $shape        Thumbnail image shape. Either 'rectangular' or 'square'.
 * @param int    $thumbnail_id Attachment ID.
 */
$shape = apply_filters( 'embed_thumbnail_image_shape', $shape, $thumbnail_id );

if ( 'rectangular' === $shape ) : ?>
	<div class="wp-embed-featured-image rectangular">
		<?php echo tribe_event_featured_image( $event_id, 'large' ); ?>
	</div>
<?php endif; ?>

<?php if ( 'square' === $shape ) : ?>
	<div class="wp-embed-featured-image square">
		<?php echo tribe_event_featured_image( $event_id, 'large' ); ?>
	</div>
<?php endif; ?>
