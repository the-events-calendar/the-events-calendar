<?php
/**
 * Models a URL passed to a view.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use \WP_MatchesMapRegex;
use Tribe__Utils__Array as Arr;

/**
 * Class Url
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
class Url {

	/**
	 * The URL abstracted by the instance.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * An array of the default URL components produced by the `parse_url` function.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected static $default_url_components = [
		'scheme'   => '',
		'host'     => '',
		'port'     => '',
		'user'     => '',
		'pass'     => '',
		'path'     => '',
		'query'    => '',
		'fragment' => '',
	];


	/**
	 * An array of the URL components as produced by the `parse_url` function.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $components = [];

	/**
	 * An array of the parsed query arguments from the URL.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $query_args = [];

	/**
	 * Which of the WP_Query regular expressions we matched this url to
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $matched_rule;

	/**
	 * Which of the WP_Query query_args we matched this url to
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $matched_query;

	/**
	 * Url constructor.
	 *
	 * @param  null|string  $url The url to build the object with or `null` to use the current URL.
	 */
	public function __construct( $url = null ) {
		if ( empty( $url ) ) {
			$url = home_url( add_query_arg( [] ) );
		}

		$this->url = $url;
		$this->parse_url();
	}

	/**
	 * Returns the slug of the view as defined in the URL.
	 *
	 * @since 4.9.2
	 *
	 * @return mixed|string The view slug as defined in the URL.
	 */
	public function get_view_slug() {
		$slug = 'default';

		if ( empty( $this->url ) ) {
			return $slug;
		}

		return Arr::get( $this->get_query_args(), 'view', $slug );
	}

	/**
	 * Returns the full URL this instance was built on.
	 *
	 * @since TBD
	 *
	 * @return string The full URL this instance was built on; an empty string if the URL is not set.
	 */
	public function __toString() {
		return $this->url;
	}

	/**
	 * Returns the current page number for the URL.
	 *
	 * @since TBD
	 *
	 * @return int The current page number if specified in the URL or the default value.
	 */
	public function get_current_page() {
		return Arr::get( $this->get_query_args(), 'paged', 1 );
	}

	/**
	 * Returns the current query arguments
	 *
	 * @since TBD
	 *
	 * @return array Returns the current Query Arguments
	 */
	public function get_query_args() {
		return $this->query_args;
	}

	/**
	 * Parses the current URL and initializes its components.
	 *
	 * @since TBD
	 */
	protected function parse_url() {
		$this->components = array_merge( static::$default_url_components, parse_url( $this->url ) );

		wp_parse_str( $this->components['query'], $query_args );
		$this->query_args = $this->parse_query_args( $query_args );
	}

	/**
	 * Returns the correct Query Arguments for a given url by matching it to the
	 * WordPress WP_Rewrite rules.
	 *
	 * Most of this functionality was copied from WP::parse_request() method,
	 * with some changes to avoid conflicts and removing non-required behaviors.
	 *
	 * @since  TBD
	 *
	 * @param  array  $extra_query_vars Extra params passed to the URL
	 *
	 * @return array
	 */
	protected function parse_query_args( $extra_query_vars = [] ) {
		/**
		 * Allows the short circuit of trying to match an URL.
		 *
		 * @since TBD
		 *
		 * @param array $query_vars       The array of requested query variables.
		 * @param array $extra_query_vars Set of extra query vars.
		 * @param self  $url              Instance of the URL we are dealing with.
		 */
		$pre_query_vars = apply_filters( 'tribe_events_views_v2_url_pre_query_args', null, $this );

		// Only short-circuit if set and array
		if ( null !== $pre_query_vars && is_array( $pre_query_vars ) ) {
			return $pre_query_vars;
		}

		global $wp_rewrite, $wp;

		$query_vars           = [];
		$post_type_query_vars = [];

		// Fetch the rewrite rules.
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		if ( ! empty( $rewrite ) ) {
			// Look for matches, removing first /.
			$request_match = ltrim( $this->components['path'], '/' );

			foreach ( (array) $rewrite as $match => $query ) {
				if (
					preg_match( "#^$match#", $request_match, $matches )
					|| preg_match( "#^$match#", urldecode( $request_match ), $matches )
				) {

					if ( $wp_rewrite->use_verbose_page_rules && preg_match( '/pagename=\$matches\[([0-9]+)\]/', $query, $varmatch ) ) {
						// This is a verbose page match, let's check to be sure about it.
						$page = get_page_by_path( $matches[ $varmatch[1] ] );
						if ( ! $page ) {
							continue;
						}

						$post_status_obj = get_post_status_object( $page->post_status );
						if (
							! $post_status_obj->public
							&& ! $post_status_obj->protected
							&& ! $post_status_obj->private
							&& $post_status_obj->exclude_from_search
						) {
							continue;
						}
					}

					// Got a match.
					$this->matched_rule = $match;
					break;
				}
			}

			if ( isset( $this->matched_rule ) ) {
				// Trim the query of everything up to the '?'.
				$query = preg_replace( '!^.+\?!', '', $query );

				// Substitute the substring matches into the query.
				$query = addslashes( WP_MatchesMapRegex::apply( $query, $matches ) );

				$this->matched_query = $query;

				// Parse the query.
				parse_str( $query, $perma_query_vars );
			}
		}

		foreach ( get_post_types( [], 'objects' ) as $post_type => $t ) {
			if (
				is_post_type_viewable( $t )
				&& $t->query_var
			) {
				$post_type_query_vars[ $t->query_var ] = $post_type;
			}
		}

		foreach ( $wp->public_query_vars as $wpvar ) {
			if ( isset( $extra_query_vars[ $wpvar ] ) ) {
				$query_vars[ $wpvar ] = $extra_query_vars[ $wpvar ];
			} elseif ( isset( $perma_query_vars[ $wpvar ] ) ) {
				$query_vars[ $wpvar ] = $perma_query_vars[ $wpvar ];
			}

			if ( ! empty( $query_vars[ $wpvar ] ) ) {
				if ( ! is_array( $query_vars[ $wpvar ] ) ) {
					$query_vars[ $wpvar ] = (string) $query_vars[ $wpvar ];
				} else {
					foreach ( $query_vars[ $wpvar ] as $vkey => $v ) {
						if ( is_scalar( $v ) ) {
							$query_vars[ $wpvar ][ $vkey ] = (string) $v;
						}
					}
				}

				if ( isset( $post_type_query_vars[ $wpvar ] ) ) {
					$query_vars['post_type'] = $post_type_query_vars[ $wpvar ];
					$query_vars['name']      = $query_vars[ $wpvar ];
				}
			}
		}

		// Convert urldecoded spaces back into +
		foreach ( get_taxonomies( [], 'objects' ) as $taxonomy => $t ) {
			if ( $t->query_var && isset( $query_vars[ $t->query_var ] ) ) {
				$query_vars[ $t->query_var ] = str_replace( ' ', '+', $query_vars[ $t->query_var ] );
			}
		}

		// Don't allow non-publicly queryable taxonomies to be queried from the front end.
		if ( ! is_admin() ) {
			foreach ( get_taxonomies( [ 'publicly_queryable' => false ], 'objects' ) as $taxonomy => $t ) {
				/*
				 * Disallow when set to the 'taxonomy' query var.
				 * Non-publicly queryable taxonomies cannot register custom query vars. See register_taxonomy().
				 */
				if ( isset( $query_vars['taxonomy'] ) && $taxonomy === $query_vars['taxonomy'] ) {
					unset( $query_vars['taxonomy'], $query_vars['term'] );
				}
			}
		}

		// Limit publicly queried post_types to those that are publicly_queryable
		if ( isset( $query_vars['post_type'] ) ) {
			$queryable_post_types = get_post_types( [ 'publicly_queryable' => true ] );
			if ( ! is_array( $query_vars['post_type'] ) ) {
				if ( ! in_array( $query_vars['post_type'], $queryable_post_types ) ) {
					unset( $query_vars['post_type'] );
				}
			} else {
				$query_vars['post_type'] = array_intersect( $query_vars['post_type'], $queryable_post_types );
			}
		}

		// Resolve conflicts between posts with numeric slugs and date archive queries.
		$query_vars = wp_resolve_numeric_slug_conflicts( $query_vars );

		foreach ( (array) $wp->private_query_vars as $var ) {
			if ( isset( $extra_query_vars[ $var ] ) ) {
				$query_vars[ $var ] = $extra_query_vars[ $var ];
			}
		}

		if ( isset( $error ) ) {
			$query_vars['error'] = $error;
		}

		/**
		 * Filters the array of parsed query variables.
		 *
		 * @since TBD
		 *
		 * @param array $query_vars       The array of requested query variables.
		 * @param array $extra_query_vars Set of extra query vars.
		 * @param self  $url              Instance of the URL we are dealing with.
		 */
		$query_vars = apply_filters( 'tribe_events_views_v2_url_query_args', $query_vars, $extra_query_vars, $this );

		return $query_vars;
	}

	/**
	 * Adds query args to the object merging them witht the current ones.
	 *
	 * @since TBD
	 *
	 * @param array $query_args An associative array of query args to add to the object.
	 *
	 * @return $this The object instance.
	 */
	public function add_query_args( array $query_args = [] ) {
		$this->query_args          = array_merge( $this->query_args, $query_args );
		$this->components['query'] = http_build_query( $this->query_args );

		return $this;
	}
}
