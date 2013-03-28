<?php
class TribeiCal {

	public static function init() {
		add_filter( 'tribe_events_list_after_template', 	array( __CLASS__, 'maybe_add_link' ), 30, 1 );
		add_filter( 'tribe_events_calendar_after_template', array( __CLASS__, 'maybe_add_link' ), 30, 1 );
		add_filter( 'tribe_events_week_after_template', 	array( __CLASS__, 'maybe_add_link' ), 30, 1 );
	}

	public static function maybe_add_link( $content ) {

		$show_ical = apply_filters( 'tribe_events_list_show_ical_link', true );

		if ( ! $show_ical )
			return $content;

		$ical    = '<a class="tribe-events-ical tribe-events-button" title="' . __( 'iCal Import', 'tribe-events-calendar' ) . '" href="' . tribe_get_ical_link() . '">' . __( '+ iCal Import', 'tribe-events-calendar' ) . '</a>';
		$content = $ical . $content;

		return $content;

	}

}