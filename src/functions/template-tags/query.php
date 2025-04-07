<?php
/**
 * Conditional tag to check if current page is an event category page
 *
 * @return bool
 **/
function tribe_is_event_category() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_category = ! empty( $wp_query->tribe_is_event_category );

	return apply_filters( 'tribe_query_is_event_category', $tribe_is_event_category );
}

/**
 * Conditional tag to check if current page is an event venue page
 *
 * @return bool
 **/
function tribe_is_event_venue() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_venue = ! empty( $wp_query->tribe_is_event_venue );

	return apply_filters( 'tribe_query_is_event_venue', $tribe_is_event_venue );
}

/**
 * Conditional tag to check if current page is an event organizer page
 *
 * @return bool
 **/
function tribe_is_event_organizer() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_organizer = ! empty( $wp_query->tribe_is_event_organizer );

	return apply_filters( 'tribe_query_is_event_organizer', $tribe_is_event_organizer );
}

/**
 * Conditional tag to check if current page is displaying event query
 *
 * @return bool
 **/
function tribe_is_event_query() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$tribe_is_event_query = ! empty( $wp_query->tribe_is_event_query );

	return apply_filters( 'tribe_query_is_event_query', $tribe_is_event_query );
}

if ( ! function_exists( 'tec_query_batch_size' ) ) {
	/**
	 * Returns the filtered chunk size to use in unlimited queries.
	 *
	 * This value will be used by the Builder in "all" type of queries.
	 * This value should be adjusted depending on the host and database server performance.
	 *
	 * @since 6.11.1
	 *
	 * @param string $context The context for which the chunk size will be used.
	 *
	 * @return int The chunk size, a positive integer.
	 */
	function tec_query_batch_size( string $context = 'default' ): int {
		/**
		 * Filters the chunk size that will be used by the query builder (and other plugin code)
		 * to fetch data with unbounded queries in chunks.
		 * This value should be adjusted depending on the host and database server performance.
		 *
		 * @since 6.11.1
		 *
		 * @param int    $chunk_size The chunk size that will be used to fetch elements in unbounded queries.
		 * @param string $context    The context for which the chunk size will be used.
		 */
		$chunk_size = apply_filters( 'tec_events_query_batch_size', 50, $context );

		if ( ! is_numeric( $chunk_size ) ) {
			$chunk_size = 50;
		} else {
			$chunk_size = (int) $chunk_size;

			if ( $chunk_size < 1 ) {
				$chunk_size = 50;
			}
		}

		return $chunk_size;
	}
}
