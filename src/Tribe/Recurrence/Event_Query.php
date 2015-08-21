<?php
/**
 * Facilitates setup of the query used to generate the /all/ events page.
 */
class Tribe__Events__Pro__Recurrence__Event_Query {
	/** @var WP_Query */
	protected $query;
	protected $slug = '';
	protected $parent_event;


	/**
	 * This is expected to be called in the context of the tribe_events_pre_get_posts
	 * action and only when it has already been determined that the request is to see
	 * all events making up a recurring sequence.
	 *
	 * @see Tribe__Events__Pro__Main::pre_get_posts()
	 *
	 * @param WP_Query $query
	 */
	public function __construct( WP_Query $query ) {
		$this->query = $query;
		$this->slug = $query->get( 'name' );

		if ( ! empty( $this->slug ) ) {
			$this->setup();
		}
	}

	/**
	 * If appropriate, mould the query to obtain all events belonging to the parent
	 * event of the sequence. Additionally may set up a filter to append a where clause
	 * to obtain the parent post in the same query.
	 */
	protected function setup() {
		unset( $this->query->query_vars['name'] );
		unset( $this->query->query_vars['tribe_events'] );

		$this->get_parent_event();

		if ( empty( $this->parent_event ) ) {
			$this->setup_for_404();
		} else {
			$this->query->set( 'post_parent', $this->parent_event->ID );
			$this->query->set( 'post_status', 'publish' );
			$this->query->set( 'posts_per_page', tribe_get_option( 'postsPerPage', 10 ) );
			$this->query->is_singular = false;
			add_filter( 'posts_where', array( $this, 'include_parent_event' ) );
		}
	}

	/**
	 * Obtains the parent event post given the slug currently being queried for.
	 */
	protected function get_parent_event() {
		$posts = get_posts( array(
			'name'        => $this->slug,
			'post_type'   => Tribe__Events__Main::POSTTYPE,
			'post_status' => 'publish',
			'numberposts' => 1,
		) );

		$this->parent_event = reset( $posts );
	}

	/**
	 * Effectively trigger a 404, ie if the provided slug was invalid.
	 */
	protected function setup_for_404() {
		$this->query->set( 'p', -1 );
	}

	/**
	 * Ensures the parent event is also included in the query results.
	 *
	 * @param  string $where_sql
	 *
	 * @return string
	 */
	public function include_parent_event( $where_sql ) {
		global $wpdb;

		// Run once only!
		remove_filter( 'posts_where', array( $this, 'include_parent_event' ) );

		$parent_id      = absint( $this->parent_event->ID );
		$where_children = " {$wpdb->posts}.post_parent = $parent_id ";
		$where_parent   = " {$wpdb->posts}.ID = $parent_id ";
		$where_either   = " ( $where_children OR $where_parent ) ";

		return str_replace( $where_children, $where_either, $where_sql );
	}
}