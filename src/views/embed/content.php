<?php
/**
 * Embed Content Template
 *
 * The content template for the embed view.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/embed/content.php
 *
 * @version 4.2
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<div class="tribe-events-single-event-description tribe-events-content">
	<?php echo tribe_events_get_the_excerpt( null, wp_kses_allowed_html( 'post' ) ); ?>
</div>
<?php
/**
 * Print additional content after the embed excerpt.
 *
 * @since 4.4.0
 */
do_action( 'embed_content' );
