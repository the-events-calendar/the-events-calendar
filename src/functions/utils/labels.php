<?php
/**
 * Utility functions for assessing the values of various post type labels.
 */

/**
 * A simple function for knowing if the "Event"/"Events" label has been changed.
 *
 * @since 4.6.3
 *
 * @return bool
 */
function tribe_is_event_label_customized() {
	return (
		'Event' !== tribe_get_event_label_singular() ||
		'event' !== tribe_get_event_label_singular_lowercase() ||
		'Events' !== tribe_get_event_label_plural() ||
		'events' !== tribe_get_event_label_plural_lowercase()
	);
}

/**
 * A simple function for knowing if the "Venue"/"Venues" label has been changed.
 *
 * @since 4.6.3
 *
 * @return bool
 */
function tribe_is_venue_label_customized() {
	return 'Venue' !== tribe_get_venue_label_singular() || 'Venues' !== tribe_get_venue_label_plural();
}

/**
 * A simple function for knowing if the "Organizer"/"Organizers" label has been changed.
 *
 * @since 4.6.3
 *
 * @return bool
 */
function tribe_is_organizer_label_customized() {
	return 'Organizer' !== tribe_get_organizer_label_singular() || 'Organizers' !== tribe_get_organizer_label_plural();
}