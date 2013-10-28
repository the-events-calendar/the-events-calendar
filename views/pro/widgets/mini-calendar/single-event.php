<?php

global $post, $wp_query;

$class = "";
if ( $wp_query->current_post == 1 ) {
	$class = ' first ';
}
if ( $wp_query->current_post+1 == $wp_query->post_count ) {
	$class .= ' last ';
}

$startDate = strtotime( $post->EventStartDate );
$endDate   = strtotime( $post->EventEndDate );
$today     = time();

/* If the event starts way in the past or ends way in the future, let's show today's date */
if ( $today > $startDate && $today < $endDate )
	$postDate = $today; else
	$postDate = $startDate;

/* If the user clicked in a particular day, let's show that day as the event date, even if the event spans a few days */
if ( defined( "DOING_AJAX" ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] == 'tribe-mini-cal-day' )
	$postDate = strtotime( $_POST["eventDate"] );
?>

<div class="tribe-mini-calendar-event event-<?php echo $wp_query->current_post;?><?php echo $class;?>">
	<div class="list-date">
		<span
			class="list-dayname"><?php echo apply_filters( 'tribe-mini_helper_tribe_events_ajax_list_dayname', date_i18n( 'D', $postDate ), $postDate, $class ); ?></span>
		<span
			class="list-daynumber"><?php echo apply_filters( 'tribe-mini_helper_tribe_events_ajax_list_daynumber', date_i18n( 'd', $postDate ), $postDate, $class ); ?></span>
	</div>
	<div class="list-info">
		<h2><a href="<?php echo tribe_get_event_link(); ?>"><?php echo $post->post_title; ?></a></h2>
		<?php if ( tribe_get_cost() ) { ?>
			<span class="tribe-mini-calendar-event-cost"><?php echo tribe_get_cost( null, true ); ?></span>
			<div class="tribe-events-divider ">|</div>
		<?php } ?>
		<?php echo tribe_events_event_schedule_details(); ?>
		<?php if ( tribe_get_venue() ) { ?>
			<div class="tribe-mini-calendar-event-venue">
				<?php echo tribe_get_venue_link( $post->ID ); ?>
			</div>
		<?php } ?>
	</div>
</div>
