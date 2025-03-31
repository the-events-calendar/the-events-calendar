<?php
/**
 * The Routes class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller;
use Tribe__Events__Rewrite;

/**
 * Class Routes.
 *
 * @since TBD
 *
 * @package TEC\Events\QR
 */
class Routes extends Controller {
	/**
	 * The route prefix for QR codes.
	 *
	 * @since TBD
	 * @var string
	 */
	private $route_prefix = 'qr';

	/**
	 * The settings options.
	 *
	 * @since TBD
	 * @var array
	 */
	private $options;

	/**
	 * The salt for QR code generation.
	 *
	 * @since TBD
	 * @var string
	 */
	private $salt;

	/**
	 * The query variable name for QR code hash.
	 *
	 * @since TBD
	 * @var string
	 */
	private $qr_hash_var = 'tec_qr_hash';

	/**
	 * Register the routes.
	 *
	 * @since TBD
	 * @return void
	 */
	public function do_register(): void {
		$this->options      = Settings::get_option_slugs();
		$this->route_prefix = tribe_get_option( $this->options['prefix'], $this->route_prefix );
		$this->salt         = substr( wp_salt( Settings::get_qr_slug() ), 0, 8 );

		$this->add_hooks();
	}

	/**
	 * Unregister the routes.
	 *
	 * @since TBD
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_hooks();
	}

	/**
	 * Adds the actions required by the controller.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function add_hooks(): void {
		add_action( 'tribe_events_pre_rewrite', [ $this, 'add_qr_rules' ] );
		add_filter( 'query_vars', [ $this, 'filter_add_query_vars' ] );
		add_filter( 'tribe_rewrite_parse_query_vars', [ $this, 'filter_parse_query_vars' ] );
	}

	/**
	 * Removes the actions required by the controller.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function remove_hooks(): void {
		remove_action( 'tribe_events_pre_rewrite', [ $this, 'add_qr_rules' ] );
		remove_filter( 'query_vars', [ $this, 'filter_add_query_vars' ] );
		remove_filter( 'tribe_rewrite_parse_query_vars', [ $this, 'filter_parse_query_vars' ] );
	}

	/**
	 * Get the route prefix.
	 *
	 * @since TBD
	 * @return string The route prefix.
	 */
	public function get_route_prefix(): string {
		return $this->route_prefix;
	}

	/**
	 * Add QR code rewrite rules.
	 *
	 * @since TBD
	 * @param Tribe__Events__Rewrite $rewrite The TEC rewrite instance.
	 * @return void
	 */
	public function add_qr_rules( Tribe__Events__Rewrite $rewrite ): void {
		$rewrite->add(
			[ 'events', $this->route_prefix, '([^/]+)' ],
			[ $this->qr_hash_var => '%1' ]
		);
	}

	/**
	 * Adds the required Query Vars for QR code routes.
	 *
	 * @since TBD
	 * @param array $query_vars The array of query variables to add to.
	 * @return array The modified query vars.
	 */
	public function filter_add_query_vars( $query_vars = [] ) {
		$query_vars[] = $this->qr_hash_var;

		return $query_vars;
	}

	/**
	 * Parse query vars for QR code routes.
	 *
	 * @since TBD
	 *
	 * @param array $query_vars The current query vars.
	 *
	 * @return array The modified query vars.
	 */
	public function filter_parse_query_vars( array $query_vars ): array {
		if ( isset( $query_vars[ $this->qr_hash_var ] ) ) {
			$query_vars[ $this->qr_hash_var ] = sanitize_text_field( $query_vars[ $this->qr_hash_var ] );
		}

		return $query_vars;
	}

	/**
	 * Generate a unique hash for a QR code.
	 *
	 * @since TBD
	 *
	 * @param int    $post_id The post ID (event or series).
	 * @param string $qr_type The QR Redirection behavior.
	 *
	 * @return string The generated hash.
	 */
	public function generate_hash( int $post_id, string $qr_type ): string {
		// Create a simple string with the data and salt.
		$data = $post_id . ':' . $qr_type . ':' . $this->salt;

		// Convert to base64url (URL-safe base64).
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * Get the URL for a QR code.
	 *
	 * @since TBD
	 *
	 * @param int    $post_id The post ID (event or series).
	 * @param string $qr_type The QR Redirection behavior.
	 *
	 * @return string The QR code URL.
	 */
	public function get_qr_url( int $post_id, string $qr_type ): string {
		$hash = $this->generate_hash( $post_id, $qr_type );

		return home_url( "events/{$this->route_prefix}/{$hash}/" );
	}

	/**
	 * Decode a QR code hash and return its information.
	 *
	 * @since TBD
	 *
	 * @param string $hash The QR code hash to decode.
	 *
	 * @throws \InvalidArgumentException If the hash is invalid.
	 *
	 * @return array{
	 *     post_id: int,
	 *     qr_type: string,
	 * } The decoded QR code information.
	 */
	public function decode_qr_hash( string $hash ): array {
		// Convert from base64url back to standard base64.
		$hash = strtr( $hash, '-_', '+/' );

		// Add padding if needed.
		$padding = strlen( $hash ) % 4;
		if ( $padding ) {
			$hash .= str_repeat( '=', 4 - $padding );
		}

		$decoded = base64_decode( $hash, true );
		if ( $decoded === false ) {
			throw new \InvalidArgumentException( 'Invalid QR code hash format.' );
		}

		// Extract the data parts.
		$parts = explode( ':', $decoded );
		if ( count( $parts ) !== 3 ) {
			throw new \InvalidArgumentException( 'Invalid QR code data format.' );
		}

		// Verify the salt matches.
		if ( $parts[2] !== $this->salt ) {
			throw new \InvalidArgumentException( 'Invalid QR code signature.' );
		}

		$post_id = (int) $parts[0];
		$qr_type = $parts[1];

		return [
			'post_id' => $post_id,
			'qr_type' => $qr_type,
		];
	}

	/**
	 * Decode a QR code URL and return its information.
	 *
	 * @since TBD
	 *
	 * @param string $url The QR code URL to decode.
	 *
	 * @throws \InvalidArgumentException If the URL is not a valid QR code URL or the hash is invalid.
	 *
	 * @return array{
	 *     post_id: int,
	 *     qr_type: string,
	 * } The decoded QR code information.
	 */
	public function decode_qr_url( string $url ): array {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $path ) {
			throw new \InvalidArgumentException( 'Invalid QR code URL.' );
		}

		$parts = explode( '/', trim( $path, '/' ) );
		if ( count( $parts ) !== 3 || $parts[0] !== 'events' || $parts[1] !== $this->route_prefix ) {
			throw new \InvalidArgumentException( 'Invalid QR code URL structure. Expected: events/' . $this->route_prefix . '/{hash}, Got: ' . $path );
		}

		return $this->decode_qr_hash( $parts[2] );
	}
}
