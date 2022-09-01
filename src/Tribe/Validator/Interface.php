<?php


interface Tribe__Events__Validator__Interface extends Tribe__Validator__Interface {

	/**
	 * Whether the provided value is an existing Venue post ID or not.
	 *
	 * @param mixed $venue_id
	 *
	 * @return bool
	 */
	public function is_venue_id( $venue_id );

	/**
	 * Whether the provided value is an existing Organizer post ID or not.
	 *
	 * @param mixed $organizer Either an array of Organizer post IDs or a single Organizer post ID.
	 *
	 * @return bool
	 */
	public function is_organizer_id( $organizer );

	/**
	 * Whether a list or array of organizer post IDs only contains valid organizer IDs or not.
	 *
	 * @param  string|array $organizers A list of organizer post IDs separated by the specified separator or an array
	 *                                  of organizer post IDs.
	 * @param string        $sep        The separator used in the list to separate the organizer post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return bool
	 */
	public function is_organizer_id_list( $organizers, $sep = ',' );

	/**
	 * Whether the event category exists or not.
	 *
	 * @param mixed  $category Either a single event category `term_id` or `slug` or an array of
	 *                     `term_id`s and `slug`s
	 * @return bool
	 */
	public function is_event_category( $category );

	/**
	 * Whether the value is the post ID of an existing event or not.
	 *
	 * @param int|string $event_id
	 *
	 * @return bool
	 */
	public function is_event_id( $event_id );

	/**
	 * Whether the value is the post name of an existing event or not.
	 *
	 * @param string $event_slug
	 *
	 * @return bool
	 */
	public function is_event_slug( $event_slug );

	/**
	 * Whether a list or array of event post IDs only contains valid event IDs or not.
	 *
	 * @since 4.6.22
	 *
	 * @param  string|array $events A list of event post IDs separated by the specified separator or an array
	 *                                  of event post IDs.
	 * @param string $sep The separator used in the list to separate the event post IDs; ignored if
	 *                                  the input value is an array.
	 *
	 * @return bool
	 */
	public function is_event_id_list( $events, $sep = ',' );

	/**
	 * Checks if the query is looking for ticketed events and Event Tickets is available.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $value Can be true or false.
	 *
	 * @return mixed
	 */
	public function supports_ticketed( $value );
}
