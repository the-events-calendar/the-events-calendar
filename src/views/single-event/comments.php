<?php
/**
 * Single Event Comments Template Part
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/single-event/comments.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link http://m.tri.be/1aiy
 *
 * @version 4.7
 *
 */
?>

<?php
if ( ! tribe_get_option( 'showComments', false ) ) {
	return false;
}

comments_template();
