<?php
/**
 * Models a URL passed to a view.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Url
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class Url {

	/**
	 * The URL abstracted by the instance.
	 *
	 * @var string
	 */
	protected $url;

	public function __construct( $url = null ) {
		$this->url = (string) $url;
	}

	/**
	 * Returns the slug of the view as defined in the URL.
	 *
	 * @return mixed|string The view slug as defined in the URL.
	 * @since TBD
	 *
	 */
	public function get_view_slug() {
		$slug = 'default';

		if ( empty( $this->url ) ) {
			return $slug;
		}

		$query = parse_url( $this->url, PHP_URL_QUERY );
		wp_parse_str( $query, $query_args );

		if ( isset( $query_args['view'] ) ) {
			$slug = $query_args['view'];
		}

		return $slug;
	}
}