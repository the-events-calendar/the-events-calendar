<?php
/*
 * A replacement for WordPress default function to short-circuit the check in the context of REST API tests.
 * When the action to verify is a WordPress REST API one, then the check is replaced with a user ID and role one.
 * Else the default, Core code is applied.
 */
function wp_verify_nonce( $nonce, $action = - 1 ) {
	if ( 'wp_rest' === $action && str_contains( $nonce, '|' ) ) {
		list( $user_role, $user_id ) = explode( '|', $nonce );
		$user = get_user_by( 'ID', (int) $user_id );

		return $user instanceof WP_User && in_array( $user_role, $user->roles, true );
	}

	// From here on, this is just a copy of the default `wp_verify_nonce` function.
	$nonce = (string) $nonce;
	$user  = wp_get_current_user();
	$uid   = (int) $user->ID;
	if ( ! $uid ) {
		/**
		 * Filters whether the user who generated the nonce is logged out.
		 *
		 * @since 3.5.0
		 *
		 * @param int    $uid    ID of the nonce-owning user.
		 * @param string $action The nonce action.
		 */
		$uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
	}

	if ( empty( $nonce ) ) {
		return false;
	}

	$token = wp_get_session_token();
	$i     = wp_nonce_tick();

	// Nonce generated 0-12 hours ago.
	$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), - 12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 1;
	}

	// Nonce generated 12-24 hours ago.
	$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), - 12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 2;
	}

	/**
	 * Fires when nonce verification fails.
	 *
	 * @since 4.4.0
	 *
	 * @param string     $nonce  The invalid nonce.
	 * @param string|int $action The nonce action.
	 * @param WP_User    $user   The current user object.
	 * @param string     $token  The user's session token.
	 */
	do_action( 'wp_verify_nonce_failed', $nonce, $action, $user, $token );

	// Invalid nonce.
	return false;
}
