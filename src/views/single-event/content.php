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
?>

<?php $event_id = $this->get( 'post_id' ); ?>
<div id="post-<?php echo absint( $event_id ); ?>" <?php post_class(); ?>>
	<?php the_content(); ?>
</div>
