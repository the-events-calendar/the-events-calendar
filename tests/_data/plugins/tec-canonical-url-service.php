<?php
/**
 * Plugin Name: TEC Canonical URL Service
 * Description: Activate the plugin and POST to /wp-json/tec-canonical/?url=url a URL to get its canonical form.
 */
add_action( 'rest_api_init', static function () {
	register_rest_route( 'tec-canonical', '/url', [
		'methods'  => 'POST',
		'callback' => static function ( WP_REST_Request $request ) {
			$url = $request->get_param( 'url' );

			if ( empty( $url ) ) {
				return new WP_Error( 'missing_url', 'Missing URL', [ 'status' => 400 ] );
			}

			echo tribe( Tribe__Events__Rewrite::class )->get_canonical_url( $url );

			die();
		},
	] );
} );
