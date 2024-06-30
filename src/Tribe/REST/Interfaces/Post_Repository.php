<?php


interface Tribe__Events__REST__Interfaces__Post_Repository {

	/**
	 * Returns an array representation of an event.
	 *
	 * @param int    $event_id An event post ID.
	 * @param string $context  Context of data.
	 *
	 * @return array|WP_Error Either the array representation of an event or an error object.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_event_data( $event_id, $context = '' );

	/**
	 * Returns an array representation of an event venue.
	 *
	 * @param int    $event_or_venue_id An event or venue post ID.
	 * @param string $context           Context of data.
	 *
	 * @return array|WP_Error Either the array representation of a venue or an error object.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_venue_data( $event_or_venue_id, $context = '' );

	/**
	 * Returns an array representation of an event organizer(s).
	 *
	 * @param int    $event_or_organizer_id An event or organizer post ID.
	 * @param string $context               Context of data.
	 *
	 * @return array|WP_Error Either an the array representation of an orgnanizer, an
	 *                        array of array representations of an event organizer or
	 *                        an error object.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_organizer_data( $event_or_organizer_id, $context = '' );

	/**
	 * Returns an array of prepared array representations of a taxonomy term.
	 *
	 * @param array $terms_data An array of term objects.
	 * @param string $taxonomy The taxonomy of the term objects.
	 *
	 * @return array|\WP_Error Either the array representation of taxonomy terms or an error object.
	 *
	 * @since 4.6
	 */
	public function prepare_terms_data( array $terms_data, $taxonomy );

	/**
	 * Prepares a single term data for the response.
	 *
	 * @since 4.6
	 *
	 * @param array  $term_data
	 * @param string $taxonomy
	 * @param string $namespace
	 *
	 * @return array
	 */
	public function prepare_term_data( $term_data, $taxonomy, $namespace );
}
