<?php

/**
 * Helper functions for the options API
 * Used to display option descriptions after they are saved
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

/**
 * display the events slug description
 *
 * @since 2.0.5
 * @author jkudish
 * @return string, the string to display
 */
function tribe_display_current_events_slug() {
 	echo '<p class="tribe-field-indent tribe-field-description description">'.__('The slug used for building the events URL.', 'tribe-events-calendar').sprintf( __('Your current Events URL is %s', 'tribe-events-calendar' ), '<code><a href="'.tribe_get_events_link().'">'.tribe_get_events_link().'</a></code>' ).'</p>';
}

/**
 * display the event single slug description
 *
 * @since 2.0.5
 * @author jkudish
 * @return string, the string to display
 */
function tribe_display_current_single_event_slug() {
 	echo '<p class="tribe-field-indent tribe-field-description description">'.sprintf( __('You <strong>cannot</strong> use the same slug as above. The above should ideally be plural, and this singular.<br />Your single Event URL is like: %s', 'tribe-events-calendar' ), '<code>'.trailingslashit( home_url() ) . tribe_get_option('singleEventSlug', 'event') . '/single-post-name/'.'</code>' ).'</p>';
}

/**
 * display the iCal description
 *
 * @since 2.0.5
 * @author jkudish
 * @return string, the string to display
 */
function tribe_display_current_ical_link() {
 	if (function_exists('tribe_get_ical_link')) {
 		echo '<p id="ical-link" class="tribe-field-indent tribe-field-description description">'.__('Here is the iCal feed URL for your events:', 'tribe-events-calendar').' '.'<code>'.tribe_get_ical_link().'</code></p>';
 	}
}