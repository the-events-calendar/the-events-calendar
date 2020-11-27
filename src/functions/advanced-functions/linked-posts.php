<?php
/**
 * The Events Calendar Template Tags for linked posts
 */

if ( ! function_exists( 'tribe_register_linked_post_type' ) ) {
	/**
	 * Registers a post type as a linked post type for events
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 * @param array $args Arguments for the linked post type - note: gets merged with get_post_type_object data
	 *
	 * @return boolean
	 */
	function tribe_register_linked_post_type( $post_type, $args = [] ) {
		return Tribe__Events__Linked_Posts::instance()->register_linked_post_type( $post_type, $args );
	}
}

if ( ! function_exists( 'tribe_deregister_linked_post_type' ) ) {
	/**
	 * Deregisters a post type as a linked post type for events
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 *
	 * @return boolean
	 */
	function tribe_deregister_linked_post_type( $post_type, $args = [] ) {
		return Tribe__Events__Linked_Posts::instance()->deregister_linked_post_type( $post_type, $args );
	}
}

if ( ! function_exists( 'tribe_has_linked_posts' ) ) {
	/**
	 * Returns whether or not there are any linked posts for the given post id
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 *
	 * @return boolean
	 */
	function tribe_has_linked_posts( $post_id ) {
		return Tribe__Events__Linked_Posts::instance()->has_linked_posts( $post_id );
	}
}

if ( ! function_exists( 'tribe_get_linked_posts' ) ) {
	/**
	 * Returns all linked posts for the given post id
	 *
	 * Post collection is indexed by post type
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 *
	 * @return array
	 */
	function tribe_get_linked_posts( $post_id ) {
		return Tribe__Events__Linked_Posts::instance()->get_linked_posts( $post_id );
	}
}

if ( ! function_exists( 'tribe_has_linked_posts_by_post_type' ) ) {
	/**
	 * Returns whether or not there are linked posts of the specified post type
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 * @param string $post_type Post type of linked posts to look for
	 *
	 * @return boolean
	 */
	function tribe_has_linked_posts_by_post_type( $post_id, $post_type ) {
		return Tribe__Events__Linked_Posts::instance()->has_linked_posts_by_post_type( $post_id, $post_type );
	}
}

if ( ! function_exists( 'tribe_get_linked_posts_by_post_type' ) ) {
	/**
	 * Returns linked posts of the specified post type
	 *
	 * @since 4.2
	 *
	 * @param int $post_id Post ID of the object
	 * @param string $post_type Post type of linked posts to look for
	 *
	 * @return array
	 */
	function tribe_get_linked_posts_by_post_type( $post_id, $post_type ) {
		return Tribe__Events__Linked_Posts::instance()->get_linked_posts_by_post_type( $post_id, $post_type );
	}
}

if ( ! function_exists( 'tribe_get_linked_post_types' ) ) {
	/**
	 * Returns the linked post types
	 *
	 * @since 4.2
	 *
	 * @return array
	 */
	function tribe_get_linked_post_types() {
		return Tribe__Events__Linked_Posts::instance()->get_linked_post_types();
	}
}

if ( ! function_exists( 'tribe_has_linked_post_types' ) ) {
	/**
	 * Returns whether or not there are any linked post types
	 *
	 * @since 4.2
	 *
	 * @return boolean
	 */
	function tribe_has_linked_post_types() {
		return Tribe__Events__Linked_Posts::instance()->has_linked_post_types();
	}
}

if ( ! function_exists( 'tribe_is_linked_post_type' ) ) {
	/**
	 * Returns whether or not the provided post type is a linked post type
	 *
	 * @since 4.2
	 *
	 * @param string $post_type Post type slug
	 *
	 * @return boolean
	 */
	function tribe_is_linked_post_type( $post_type ) {
		return Tribe__Events__Linked_Posts::instance()->is_linked_post_type( $post_type );
	}
}

if ( ! function_exists( 'tribe_link_post' ) ) {
	/**
	 * Links two posts together
	 *
	 * @since 4.2
	 *
	 * @param int $target_post_id Post ID of post to add linked post to
	 * @param int $subject_post_id Post ID of post to add as a linked post to the target
	 *
	 * @return boolean
	 */
	function tribe_link_post( $target_post_id, $subject_post_id ) {
		return Tribe__Events__Linked_Posts::instance()->link_post( $target_post_id, $subject_post_id );
	}
}

if ( ! function_exists( 'tribe_unlink_post' ) ) {
	/**
	 * Unlinks two posts from eachother
	 *
	 * @since 4.2
	 *
	 * @param int $target_post_id Post ID of post to remove linked post from
	 * @param int $subject_post_id Post ID of post to remove as a linked post from the target
	 */
	function tribe_unlink_post( $target_post_id, $subject_post_id ) {
		return Tribe__Events__Linked_Posts::instance()->unlink_post( $target_post_id, $subject_post_id );
	}
}

if ( ! function_exists( 'tribe_get_linked_post_container' ) ) {
	/**
	 * Returns the post type's form field container name
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type
	 *
	 * @return string
	 */
	function tribe_get_linked_post_container( $post_type ) {
		return Tribe__Events__Linked_Posts::instance()->get_post_type_container( $post_type );
	}
}

if ( ! function_exists( 'tribe_get_linked_post_id_field_index' ) ) {
	/**
	 * Returns the post type's ID field name
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type
	 *
	 * @return string
	 */
	function tribe_get_linked_post_id_field_index( $post_type ) {
		return Tribe__Events__Linked_Posts::instance()->get_post_type_id_field_index( $post_type );
	}
}

if ( ! function_exists( 'tribe_get_linked_post_name_field_index' ) ) {
	/**
	 * Returns the post type's name field
	 *
	 * @since 4.2
	 *
	 * @param string $linked_post_type Linked post type
	 *
	 * @return string
	 */
	function tribe_get_linked_post_name_field_index( $post_type ) {
		return Tribe__Events__Linked_Posts::instance()->get_post_type_name_field_index( $post_type );
	}
}
