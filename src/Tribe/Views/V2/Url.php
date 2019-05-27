<?php
/**
 * Models a URL passed to a view.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

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
	 * @var string
	 */
	protected $url = '';

	/**
	 * An array of the default URL components produced by the `parse_url` function.
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
	 * @var
	 */
	protected $components;

	/**
	 * An array of the parsed query arguments from the URL.
	 *
	 * @var array
	 */
	protected $query_args = [];

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
	 * @return mixed|string The view slug as defined in the URL.
	 * @since 4.9.2
	 *
	 */
	public function get_view_slug() {
		$slug = 'default';

		if ( empty( $this->url ) ) {
			return $slug;
		}

		if ( isset( $this->query_args['view'] ) ) {
			$slug = $this->query_args['view'];
		}

		return $slug;
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
		return isset( $this->query_args['paged'] )
			? $this->query_args['paged']
			: 1;
	}

	/**
	 * Parses the current URL and initializes its components.
	 *
	 * @since TBD
	 */
	protected function parse_url() {
		$this->components = array_merge( static::$default_url_components, parse_url( $this->url ) );

		wp_parse_str( $this->components['query'], $query_args );
		$this->query_args = $query_args;
	}

	public function add_query_args( array $query_args = [] ) {
		$this->query_args          = array_merge( $this->query_args, $query_args );
		$this->components['query'] = http_build_query( $this->query_args );

		return $this;
	}
}