<?php
/**
 * Condensed List View Loop
 * This file sets up the structure for the list loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/loop.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<?php
global $post;
global $more;
$more = false;
?>

<div class="tribe-events-loop">

	<?php while ( have_posts() ) : the_post(); ?>
		<?php do_action( 'tribe_events_inside_before_loop' ); ?>

		<!-- Event  -->
		<?php
		$post_parent = '';
		if ( $post->post_parent ) {
			$post_parent = ' data-parent-post-id="' . absint( $post->post_parent ) . '"';
		}
		?>
		<div id="post-<?php the_ID() ?>" class="<?php tribe_events_event_classes() ?>" <?php echo $post_parent; ?>>
			<?php tribe_get_template_part( 'list-condensed/single', 'event' ) ?>
		</div>


		<?php do_action( 'tribe_events_inside_after_loop' ); ?>
	<?php endwhile; ?>

</div><!-- .tribe-events-loop -->
