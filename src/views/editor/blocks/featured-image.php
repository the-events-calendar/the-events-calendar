<?php
/**
 * Block: Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/editor/blocks/featured-image.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event_id = $this->get( 'post_id' );
?>
<?php echo tribe_event_featured_image( $event_id, 'full', false );