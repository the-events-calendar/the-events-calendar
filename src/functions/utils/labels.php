<?php
/**
 * Utility functions for assessing the values of various post type labels.
 */

/**
 * A simple function for knowing if the "Event"/"Events" label has been changed.
 *
 * @return bool
 */
function tribe_is_event_label_customized() {

	if (
		'Event'  !== tribe_get_event_label_singular() ||
		'event'  !== tribe_get_event_label_singular_lowercase() ||
		'Events' !== tribe_get_event_label_plural() ||
		'events' !== tribe_get_event_label_plural_lowercase()
	) {
		return true;
	}

	return false;
}

/**
 * A simple function for knowing if the "Venue"/"Venues" label has been changed.
 *
 * @return bool
 */
function tribe_is_venue_label_customized() {

	if (
		'Venue'  !== tribe_get_venue_label_singular() ||
		'Venues' !== tribe_get_venue_label_plural()
	) {
		return true;
	}

	return false;
}

/**
 * A simple function for knowing if the "Organizer"/"Organizers" label has been changed.
 *
 * @return bool
 */
function tribe_is_organizer_label_customized() {

	if (
		'Organizer'  !== tribe_get_organizer_label_singular() ||
		'Organizers' !== tribe_get_organizer_label_plural()
	) {
		return true;
	}

	return false;
}