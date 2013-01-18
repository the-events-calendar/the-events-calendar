<?php
$class = "";
if ( $cont == 1 ) {
	$class = ' first ';
}
if ( $cont == $args['count'] ) {
	$class .= ' last ';
}

$startDate = strtotime( $event->EventStartDate );
$endDate   = strtotime( $event->EventEndDate );
$today     = time();


/* If the event starts way in the past or ends way in the future, let's show today's date */
if ( $today > $startDate && $today < $endDate )
	$eventDate = $today; else
	$eventDate = $startDate;

/* If the user clicked in a particular day, let's show that day as the event date, even if the event spans a few days */
if ( defined( "DOING_AJAX" ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] == 'ts-calendar-day' )
	$eventDate = strtotime( $_POST["eventDate"] );


?>
<div class="tribe-mini-calendar-event event-<?php echo $cont;?><?php echo $class;?>">
	<div class="list-date">
		<span
			class="list-dayname"><?php echo apply_filters( 'tribe-mini_helper_tribe_events_ajax_list_dayname', date( 'l', $eventDate ), $eventDate, $class ); ?></span>
		<span
			class="list-daynumber"><?php echo apply_filters( 'tribe-mini_helper_tribe_events_ajax_list_daynumber', date( 'd', $eventDate ), $eventDate, $class ); ?></span>
	</div>
	<div class="list-info">
		<h2><a href="<?php echo get_permalink( $event->ID ); ?>"><?php echo $event->post_title; ?></a></h2>

		<p><?php echo $event->post_excerpt; ?></p>
		<span><?php echo tribe_get_venue_link( $event->ID ); ?></span>
	</div>
</div>