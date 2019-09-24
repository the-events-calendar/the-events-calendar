<?php
/**
 * Models a URL passed to a view.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe__Context as Context;
use Tribe__Events__Rewrite as Rewrite;
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
	 * @since 4.9.3
	 *
	 * @var string
	 */
	protected $url = '';

	/**
	 * An array of the default URL components produced by the `parse_url` function.
	 *
	 * @since 4.9.3
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
	 * @since 4.9.3
	 *
	 * @var array
	 */
	protected $components = [];

	/**
	 * An array of the parsed query arguments from the URL.
	 *
	 * @since 4.9.3
	 *
	 * @var array
	 */
	protected $query_args = [];

	/**
	 * A flag to define how conflicts between parameters set in the query arguments and parameters set by the path
	 * should be resolved.
	 * If `false` then arguments parsed from the path will override the query ones, if `false` the arguments parsed from
	 * the query will override the path ones.
	 *
	 * @var bool
	 */
	protected $query_overrides_path = false;

	/**
	 * Url constructor.
	 *
	 * @param null|string $url The url to build the object with or `null` to use the current URL.
	 * @param bool $query_overrides_path A flag to define how conflicts between parameters set in the query
	 *                                   arguments and parameters set by the path should be resolved.
	 */
	public function __construct( $url = null, $query_overrides_path = false ) {
		if ( empty( $url ) ) {
			$url = home_url( add_query_arg( [] ) );
		}

		$this->url = $url;
		$this->query_overrides_path = (bool)$query_overrides_path;
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

		return Arr::get_first_set( $this->get_query_args(), [ 'view', 'tribe_view', 'eventDisplay' ], $slug );
	}

	/**
	 * Returns the full URL this instance was built on.
	 *
	 * @since 4.9.3
	 *
	 * @return string The full URL this instance was built on; an empty string if the URL is not set.
	 */
	public function __toString() {
		return tribe_build_url( $this->components );
	}

	/**
	 * Returns the current page number for the URL.
	 *
	 * @since 4.9.3
	 *
	 * @return int The current page number if specified in the URL or the default value.
	 */
	public function get_current_page() {
		return Arr::get_first_set( $this->get_query_args(), [ 'paged', 'page' ], 1 );
	}

	/**
	 * Returns the current query arguments
	 *
	 * @since 4.9.3
	 *
	 * @return array Returns the current Query Arguments
	 */
	public function get_query_args() {
		return $this->query_args;
	}

	/**
	 * Parses the current URL and initializes its components.
	 *
	 * @since 4.9.3
	 *
	 * @return Url This object instance.
	 */
	public function parse_url() {
		$this->components = array_merge( static::$default_url_components, parse_url( $this->url ) );
		$this->query_args = Rewrite::instance()->parse_request( $this->url );
		if ( ! empty( $this->components['query'] ) ) {
			parse_str( $this->components['query'], $query_component_args );
			$this->query_args     = $this->query_overrides_path
				? array_merge( $this->query_args, $query_component_args )
				: array_merge( $query_component_args, $this->query_args );
		}

		return $this;
	}

	/**
	 * Adds query args to the object merging them witht the current ones.
	 *
	 * @since 4.9.3
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

	/**
	 * Sets whether the parameters set in the query should override the ones parsed by the path or not.
	 *
	 * By default path parameters will take precedence over query parameters.
	 * When set to `false`  then `/events/list?eventDisplay=month` will result in an `eventDisplay=list`;
	 * when set to `true` the resulting `eventDisplay` will be `month`.
	 *
	 * @since 4.9.3
	 *
	 * @param bool $query_overrides_path Whether the parameters set in the query should override the ones parsed by the
	 *                                   path or not.
	 *
	 * @return Url This object instance to chain method calls.
	 */
	public function query_overrides_path( $query_overrides_path ) {
		$this->query_overrides_path = (bool) $query_overrides_path;

		return $this;
	}

	/**
	 * Returns the alias of the variable set in the Url query args, if any.
	 *
	 * @since 4.9.4
	 *
	 * @param              string $var The name of the variable to search an alias for.
	 * @param Context|null $context The Context object to use to fetch locations, if `null` the global Context will be
	 *                              used.
	 *
	 * @return false|string The variable alias set in the URL query args, or `false` if no alias was found.
	 */
	public function get_query_arg_alias_of( $var, Context $context = null ) {
		$aliases = $this->get_query_args_aliases_of( $var, $context, false );


		return count( $aliases ) ? reset( $aliases ) : false;
	}

	/**
	 * Returns the value of a query arg set on the URL, or a default value if not found.
	 *
	 * @since 4.9.4
	 *
	 * @param      string $key The
	 * @param null $default
	 *
	 * @return mixed
	 */
	public function get_query_arg( $key, $default = null ) {
		return Arr::get( (array) $this->get_query_args(), $key, $default );
	}

	/**
	 * Returns all the aliases of the variable set in the Url query args, if any.
	 *
	 * @since 4.9.9
	 *
	 * @param string       $var     The name of the variable to search the aliases for.
	 * @param Context|null $context The Context object to use to fetch locations, if `null` the global Context will be
	 *                              used.
	 *
	 * @return array An array of the variable aliases set in the URL query args.
	 */
	public function get_query_args_aliases_of( $var, Context $context = null ) {
		$context    = $context ?: tribe_context();
		$query_args = $this->get_query_args();
		$aliases    = $context->translate_sub_locations(
			$query_args,
			Context::QUERY_VAR,
			'read'
		);

		if ( empty( $aliases ) ) {
			return [];
		}

		$query_aliases   = (array) Arr::get( $context->get_locations(), [ $var, 'read', Context::QUERY_VAR ], [] );
		$request_aliases = (array) Arr::get( $context->get_locations(), [ $var, 'read', Context::REQUEST_VAR ], [] );
		$context_aliases = array_unique( array_merge( $query_aliases, $request_aliases ) );

		$aliases = array_intersect_key(
			array_merge( $query_args, tribe_get_request_vars() ),
			array_merge( $aliases, array_combine( $context_aliases, $context_aliases ) )
		);

		return array_keys( $aliases );
	}
}
