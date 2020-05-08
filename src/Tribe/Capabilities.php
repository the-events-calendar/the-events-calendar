<?php

class Tribe__Events__Capabilities {

	/**
	 * The transient key for delayed capabilities updates.
	 *
	 * @since 5.1.1
	 *
	 * @var string
	 */
	public static $key_needs_init = '_tribe_events_needs_capability_init';

	/**
	 * An array of roles to update capabilities.
	 *
	 * @since 5.1.1
	 *
	 * @var array
	 */
	protected $roles = [
		'administrator',
		'editor',
		'author',
		'contributor',
		'subscriber',
	];

	/**
	 * An array of capabilities aliases by role.
	 *
	 * @since 5.1.1
	 *
	 * @var array
	 */
	private $cap_aliases = [
		// Full permissions to a post type.
		'editor'      => [
			'read',
			'read_private_posts',
			'edit_posts',
			'edit_others_posts',
			'edit_private_posts',
			'edit_published_posts',
			'delete_posts',
			'delete_others_posts',
			'delete_private_posts',
			'delete_published_posts',
			'publish_posts',
		],
		// Full permissions for content the user created.
		'author'      => [
			'read',
			'edit_posts',
			'edit_published_posts',
			'delete_posts',
			'delete_published_posts',
			'publish_posts',
		],
		// Create, but not publish.
		'contributor' => [
			'read',
			'edit_posts',
			'delete_posts',
		],
		// Read only.
		'subscriber'  => [
			'read',
		],
	];

	/**
	 * @deprecated 5.1.1
	 *
	 * @var bool
	 */
	public $set_initial_caps = false;

	/**
	 * Hook up the correct methods to the places required to setup the capabilities.
	 *
	 * @since 5.1.1
	 */
	public function hook() {
		// Update Capabilities.
		add_action( 'wp_loaded', [ $this, 'set_initial_caps' ], 10, 0 );
	}

	/**
	 * Set the transient for flagging the transients needs a initialization.
	 *
	 * @since 5.1.1
	 *
	 * @return bool Status of set_transient.
	 */
	public function set_needs_init() {
		return set_transient( static::$key_needs_init, 'yes', DAY_IN_SECONDS );
	}

	/**
	 * Deletes the transient for flagging the transients needs a initialization.
	 *
	 * @since 5.1.1
	 *
	 * @return bool Status of delete_transient.
	 */
	public function delete_needs_init() {
		return delete_transient( static::$key_needs_init );
	}

	/**
	 * Determines if capabilities need initialization on this request.
	 *
	 * @since 5.1.1
	 *
	 * @return bool Caps need initialisation.
	 */
	public function needs_init() {
		return tribe_is_truthy( get_transient( static::$key_needs_init ) );
	}

	/**
	 * Get the Roles to Modify Capabilities.
	 *
	 * @since 5.1.1
	 *
	 * @return array An array of roles to modify capabilities.
	 */
	public function get_roles() {

		/**
		 * Filters the Roles for Tribe Events Capabilities.
		 *
		 * @since 5.1.1
		 *
		 * @param array $roles An array of roles to add capabilities.
		 */
		return apply_filters( 'tribe_events_filter_roles_with_capabilities', $this->roles );
	}

	/**
	 * Grant caps for the given post type to the given role
	 *
	 * @param string $post_type The post type to grant caps for
	 * @param string $role_id The role receiving the caps
	 * @param string $level The capability level to grant (see the list of caps above)
	 *
	 * @return bool false if the action failed for some reason, otherwise true
	 */
	public function register_post_type_caps( $post_type, $role_id, $level = '' ) {
		if ( empty( $level ) ) {
			$level = $role_id;
		}

		if ( 'administrator' === $level ) {
			$level = 'editor';
		}

		if ( ! isset( $this->cap_aliases[ $level ] ) ) {
			return false;
		}

		$role = get_role( $role_id );
		if ( ! $role ) {
			return false;
		}

		$pto = get_post_type_object( $post_type );
		if ( empty( $pto ) ) {
			return false;
		}

		foreach ( $this->cap_aliases[ $level ] as $alias ) {
			if ( isset( $pto->cap->$alias ) ) {
				$role->add_cap( $pto->cap->$alias );
			}
		}

		return true;
	}

	/**
	 * Remove all caps for the given post type from the given role
	 *
	 * @param string $post_type The post type to remove caps for
	 * @param string $role_id The role which is losing caps
	 *
	 * @return bool false if the action failed for some reason, otherwise true
	 */
	public function remove_post_type_caps( $post_type, $role_id ) {
		$role = get_role( $role_id );
		if ( ! $role ) {
			return false;
		}
		foreach ( $role->capabilities as $cap => $has ) {
			if ( strpos( $cap, $post_type ) !== false ) {
				$role->remove_cap( $cap );
			}
		}

		return true;
	}

	/**
	 * Set the initial capabilities for events and related post types on default roles
	 *
	 * @since 5.1.1 - use get_roles() method, add check for transient.
	 *
	 * @param boolean $force Force the registering of new caps without checking any flags.
	 *
	 * @return void
	 */
	public function set_initial_caps( $force = false ) {
		// Allows bailing on check for needs init.
		if ( ! $force && ! $this->needs_init() ) {
			return;
		}

		foreach ( $this->get_roles() as $role ) {
			$this->register_post_type_caps( Tribe__Events__Main::POSTTYPE, $role );
			$this->register_post_type_caps( Tribe__Events__Main::VENUE_POST_TYPE, $role );
			$this->register_post_type_caps( Tribe__Events__Main::ORGANIZER_POST_TYPE, $role );
			$this->register_post_type_caps( Tribe__Events__Aggregator__Records::$post_type, $role );
		}

		$this->delete_needs_init();
	}

	/**
	 * Remove capabilities for events and related post types from default roles
	 *
	 * @since 5.1.1 - use get_roles() method.
	 *
	 * @return void
	 */
	public function remove_all_caps() {

		foreach ( $this->get_roles() as $role ) {
			$this->remove_post_type_caps( Tribe__Events__Main::POSTTYPE, $role );
			$this->remove_post_type_caps( Tribe__Events__Main::VENUE_POST_TYPE, $role );
			$this->remove_post_type_caps( Tribe__Events__Main::ORGANIZER_POST_TYPE, $role );
			$this->remove_post_type_caps( Tribe__Events__Aggregator__Records::$post_type, $role );
		}
	}
}
