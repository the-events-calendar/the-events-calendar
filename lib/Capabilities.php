<?php


class Tribe__Events__Capabilities {
	private $cap_aliases = array(
		'editor' => array( // full permissions to a post type
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
		),
		'author' => array( // full permissions for content the user created
			'read',
			'edit_posts',
			'edit_published_posts',
			'delete_posts',
			'delete_published_posts',
			'publish_posts',
		),
		'contributor' => array( // create, but not publish
			'read',
			'edit_posts',
			'delete_posts',
		),
		'subscriber' => array( // read only
			'read',
		),
	);

	public function register_post_type_caps( $post_type, $role_id, $level = '' ) {
		if ( empty( $level ) ) {
			$level = $role_id;
		}
		if ( $level == 'administrator' ) {
			$level = 'editor';
		}
		if ( !isset( $this->cap_aliases[$level] ) ) {
			return FALSE;
		}
		$role = get_role( $role_id );
		if ( !$role ) {
			return FALSE;
		}
		$pto = get_post_type_object( $post_type );

		foreach ( $this->cap_aliases[$level] as $alias ) {
			if ( isset( $pto->cap->$alias ) ) {
				$role->add_cap( $pto->cap->$alias );
			}
		}
		return TRUE;
	}

	/**
	 * Set the initial capabilities for events and related post types
	 *
	 * @return void
	 */
	public function set_initial_caps() {
		foreach( array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) as $role ) {
			$this->register_post_type_caps( TribeEvents::POSTTYPE, $role );
			$this->register_post_type_caps( TribeEvents::ORGANIZER_POST_TYPE, $role );
			$this->register_post_type_caps( TribeEvents::VENUE_POST_TYPE, $role );
		}
	}
}