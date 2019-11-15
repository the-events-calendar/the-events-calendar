<?php
/**
 * Single Event Content Template Part
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event/content.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7
 *
 */

$event_id = $this->get( 'post_id' );
$content = get_the_content( null, false, $event_id );

/**
 * Filters the post content.
 *
 * @since 0.71
 *
 * @param string $content Content of the current post.
 */
$content = apply_filters( 'the_content', $content );
$content = str_replace( ']]>', ']]&gt;', $content );
?>
<div id="post-<?php echo absint( $event_id ); ?>" <?php post_class(); ?>>
	<?php echo $content; ?>
</div>
