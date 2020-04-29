<?php

class Tribe__Events__Capabilities {

	/**
	 * The transient key for delayed capabilities updates.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $key_delayed_capabilities_update = '_tribe_events_delayed_capabilities_update';

	/**
	 * An array of roles to update capabilities.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @var array
	 */
	private $cap_aliases = [
		'editor'      => [ // full permissions to a post type
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
		'author'      => [ // full permissions for content the user created
		                   'read',
		                   'edit_posts',
		                   'edit_published_posts',
		                   'delete_posts',
		                   'delete_published_posts',
		                   'publish_posts',
		],
		'contributor' => [ // create, but not publish
		                   'read',
		                   'edit_posts',
		                   'delete_posts',
		],
		'subscriber'  => [ // read only
		                   'read',
		],
	];

	/**
	 * @deprecated TBD
	 *
	 * @var bool
	 */
	public $set_initial_caps = false;

	/**
	 * Initialize Setting the Capabilities.
	 *
	 * @since TBD
	 */
	public function init_set_caps() {

		set_transient( $this->key_delayed_capabilities_update, 'yes', 0 );

	}

	/**
	 * Get the Roles to Modify Capabilities.
	 *
	 * @since TBD
	 *
	 * @return array An array of roles to modify capabilities.
	 */
	public function get_roles() {

		/**
		 * Filters the Roles for Tribe Events Capabilities.
		 *
		 * @since TBD
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
	 * @since TBD - use get_roles() method, add check for transient.
	 *
	 * @return void
	 */
	public function set_initial_caps() {

		$should_update_capabilities = tribe_is_truthy( get_transient( $this->key_delayed_capabilities_update ) );

		if ( ! $should_update_capabilities ) {
			return;
		}

		foreach ( $this->get_roles() as $role ) {
			$this->register_post_type_caps( Tribe__Events__Main::POSTTYPE, $role );
			$this->register_post_type_caps( Tribe__Events__Main::VENUE_POST_TYPE, $role );
			$this->register_post_type_caps( Tribe__Events__Main::ORGANIZER_POST_TYPE, $role );
			$this->register_post_type_caps( Tribe__Events__Aggregator__Records::$post_type, $role );
		}

		delete_transient( $this->key_delayed_capabilities_update );
	}

	/**
	 * Remove capabilities for events and related post types from default roles
	 *
	 * @since TBD - use get_roles() method.
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
