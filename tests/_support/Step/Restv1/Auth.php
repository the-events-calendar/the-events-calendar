<?php

namespace Step\Restv1;

use Symfony\Component\BrowserKit\Cookie;

trait Auth  {
	/**
	 * This map is hard-coded and comes from the fixture, do not change it manually if not following a
	 * database fixture update.
	 * Each user login and password is the role name, e.g. the login and password for the Contributor user are both
	 * `contributor`.
	 *
	 * @var array<string,int>
	 */
	protected static $role_to_user_id_map = [
		'administrator' => 1,
		'editor'        => 4,
		'contributor'   => 3,
		'subscriber'    => 2,
	];

	/**
	 * An map storing each user generated cookies by user ID.
	 *
	 * @var array<int,array<Cookie>>
	 */
	protected static $users_cookies =  [];

	/**
	 * Authenticates a user with a role for the scope of the test.
	 *
	 * The method will create a user in WordPress with the "user" login and password, create a valid "wp_rest" nonce
	 * for the user and set the nonce on the "X-WP-Nonce" header.
	 *
	 * @param string $role A valid WordPress user role, e.g. 'subscriber' or `administrator`; use 'visitor' to indicate
	 *                     a user with an ID of 0.
	 *
	 * @see https://codex.wordpress.org/Roles_and_Capabilities#Summary_of_Roles
	 *
	 * @return string The generated and valid nonce.
	 */
	public function generate_nonce_for_role( $role ): string {
		$I = $this;

		$user_id    = 0;
		$user_login = 'user';
		$user_pass  = 'user';

		if ( isset( static::$role_to_user_id_map[ $role ] ) ) {
			// Let's avoid creating the user if we can.
			$user_id    = static::$role_to_user_id_map[ $role ];
			$user_login = $user_pass = $role;
			/*
			 * @todo The natural evolution of this is to avoid the `loginAs` call entirely.
			 * The logic to restore the user session (including cookies) correctly for the purpose of nonce
			 * generation, though, is not that straightforward and will require a bit more work.
			 */
		} elseif ( 'visitor' !== $role ) {
			$user_id = $I->haveUserInDatabase( 'user', $role, [ 'user_pass' => 'user' ] );
		}

		// Login to get the cookies, do not store the cookies.
		$this->loginAs( $user_login, $user_pass );
		// Set this cookie in the variable space to allow for the correct generation of the nonce.
		$_COOKIE[ LOGGED_IN_COOKIE ] = $this->grabCookie( LOGGED_IN_COOKIE );

		// Generate the nonce for the logged in user.
		$nonce = $this->generate_nonce_for_user( $user_id, $user_login,$user_pass );

		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		return $nonce;
	}

	/**
	 * Generate a WP REST API nonce value for the specified user details.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return string The generated WP REST API nonce.
	 *
	 * @throws \RuntimeException If the nonce cannot be generated for the user.
	 */
	protected function generate_nonce_for_user( $user_id, $user_login,$user_pass ) {
		$credentials = [
			'user_login'    => $user_login,
			'user_password' => $user_pass
		];

		if ( ! wp_signon( $credentials ) instanceof \WP_User ) {
			throw new \RuntimeException( "Could not sign in as user {$user_login} with password {$user_pass}." );
		}

		wp_set_current_user( $user_id );

		$nonce = wp_create_nonce( 'wp_rest' );

		if ( empty( $nonce ) ) {
			throw new \RuntimeException(
				"Could not generate nonce for user {$user_id}."
			);
		}

		return $nonce;
	}
}
