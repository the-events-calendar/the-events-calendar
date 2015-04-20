<?php
/**
 * Related Events Template
 * The template for displaying related events on the single event page.
 *
 * You can recreate an ENTIRELY new related events view by doing a template override, and placing
 * a related-events.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/related-events.php.
 *
 * You can use any or all filters included in this file or create your own filters in
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters
 *
 * @package TribeEventsCalendarPro
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$posts = tribe_get_related_posts();

?>

<?php
if ( is_array( $posts ) && ! empty( $posts ) ) {
	echo '<h3 class="tribe-events-related-events-title">' . __( 'Related Events', 'tribe-events-calendar-pro' ) . '</h3>';
	echo '<ul class="tribe-related-events tribe-clearfix hfeed vcalendar">';
	foreach ( $posts as $post ) {
		echo '<li>';

		$thumb = ( has_post_thumbnail( $post->ID ) ) ? get_the_post_thumbnail( $post->ID, 'large' ) : '<img src="' . trailingslashit( TribeEventsPro::instance()->pluginUrl ) . 'resources/images/tribe-related-events-placeholder.png" alt="' . get_the_title( $post->ID ) . '" />';;
		echo '<div class="tribe-related-events-thumbnail">';
		echo '<a href="' . tribe_get_event_link( $post ) . '" class="url" rel="bookmark">' . $thumb . '</a>';
		echo '</div>';
		echo '<div class="tribe-related-event-info">';
		echo '<h3 class="tribe-related-events-title summary"><a href="' . tribe_get_event_link( $post ) . '" class="url" rel="bookmark">' . get_the_title( $post->ID ) . '</a></h3>';

		if ( $post->post_type == TribeEvents::POSTTYPE ) {
			echo tribe_events_event_schedule_details( $post );
		}
		echo '</div>';
		echo '</li>';
	}
	echo '</ul>';
}
?>
