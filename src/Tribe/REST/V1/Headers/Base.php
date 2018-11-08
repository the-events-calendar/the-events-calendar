<?php


class Tribe__Events__REST__V1__Headers__Base implements Tribe__REST__Headers__Base_Interface {

	/**
	 * @var string
	 */
	protected $api_version_header = 'X-TEC-API-VERSION';
	/**
	 * @var string
	 */
	protected $api_root_header = 'X-TEC-API-ROOT';
	/**
	 * @var string
	 */
	protected $api_origin_header = 'X-TEC-API-ORIGIN';
	/**
	 * @var string
	 */
	protected $api_version_meta_name = 'tec-api-version';
	/**
	 * @var string
	 */
	protected $api_origin_meta_name = 'tec-api-origin';

	/**
	 * Returns the header that the REST API will print on the page head to report
	 * its version.
	 *
	 * @return string
	 */
	public function get_api_version_header() {
		return $this->api_version_header;
	}

	/**
	 * Returns the header the REST API will print on the page head to report its root
	 * url.
	 *
	 * @return string
	 */
	public function get_api_root_header() {
		return $this->api_root_header;
	}

	/**
	 * Returns the header the REST API will print on the page head to report its origin
	 * url. Normaly the home_url()
	 *
	 * @return string
	 */
	public function get_api_origin_header() {
		return $this->api_origin_header;
	}

	/**
	 * Returns the `name` of the meta tag that will be printed on the page to indicate
	 * the REST API version.
	 *
	 * @return string
	 */
	public function get_api_version_meta_name() {
		return $this->api_version_meta_name;
	}

	/**
	 * Returns the `name` of the meta tag that will be printed on the page to indicate
	 * the REST API Origin URL.
	 *
	 * @return string
	 */
	public function get_api_origin_meta_name() {
		return $this->api_origin_meta_name;
	}

	/**
	 * Returns the REST API URL.
	 *
	 * @return string
	 */
	public function get_rest_url() {
		if ( is_single() && tribe_is_event() ) {
			$this_post_id = Tribe__Main::post_id_helper();

			/**
			 * We are dealing with an /all kind of query.
			 * The root URL has to be modified to include the
			 * post parent and its children post IDs.
			 */
			if (
				( $wp_query = tribe_get_global_query_object() )
				&& ( $post_parent = $wp_query->get( 'post_parent' ) )
				&& $post_parent == $this_post_id
			) {
				/**
				 * Filters the `posts_per_page` value that should be used to fetch children
				 * posts to the currently queried one.
				 *
				 * This is typically happening when generating the root REST URL for a recurring event
				 * (from The Events Calendar PRO). The children post IDs are fetched to build an `include`
				 * request for The Events Calendar REST API and the REST API will deal with pagination.
				 *
				 * @since 4.6.22
				 *
				 * @param int $posts_per_page How many children posts to include in the query at the most;
				 *                            defaults to `-1` to fetch them all.
				 * @param int $post_parent The post ID of the queried event.
				 * @param WP_Query The current WP_Query object.
				 */
				$posts_per_page = apply_filters( 'tribe_rest_event_parent_include_per_page', - 1, $post_parent, $wp_query );

				$all            = array( $post_parent );
				$children       = get_posts( array(
					'post_type'      => Tribe__Events__Main::POSTTYPE,
					'fields'         => 'ids',
					'posts_per_page' => $posts_per_page,
					'post_parent'    => $post_parent,
				) );
				if ( ! empty( $children ) && is_array( $children ) ) {
					sort( $children );
					$all = array_merge( $all, $children );
				}

				return add_query_arg( array(
					'include' => Tribe__Utils__Array::to_list( $all ),
				), tribe_events_rest_url( '/events' ) );
			}

			return tribe_events_rest_url( 'events/' . $this_post_id );
		}

		/** @var WP_Query $wp_query */
		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		$is_featured = (bool) $wp_query->get( 'featured', false );
		if ( $is_featured ) {
			return add_query_arg( array( 'featured' => true ), tribe_events_rest_url( 'events/' ) );
		}

		if ( ! empty( $wp_query->tribe_is_event_category ) && $wp_query->tribe_is_event_category ) {
			$category = $wp_query->get( Tribe__Events__Main::TAXONOMY );

			return add_query_arg( array( 'categories' => $category ), tribe_events_rest_url( 'events/' ) );
		}

		if ( $wp_query->is_tag ) {
			$tag = $wp_query->get( 'tag' );

			return add_query_arg( array( 'tags' => $tag ), tribe_events_rest_url( 'events/' ) );
		}

		if ( $wp_query->get( 'post_type' ) === Tribe__Events__Venue::POSTTYPE ) {
			$venue = $wp_query->get( 'name' );
			if ( ! empty( $wp_query->queried_object_id ) && is_numeric( $wp_query->queried_object_id ) ) {
				$venue = $wp_query->queried_object_id;
			}
			return add_query_arg( array( 'venue' => $venue ), tribe_events_rest_url( 'events/' ) );
		}

		return tribe_events_rest_url();
	}

	/**
	 * Returns the REST API Origin Site.
	 *
	 * @return string
	 */
	public function get_rest_origin_url() {
		return home_url();
	}
}
