<?php
/**
 * Manages the External Calendar Embeds Feature.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use RuntimeException;
use WP_Post_Type;

/**
 * Class Calendar_Embeds
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */
class Calendar_Embeds {

	/**
	 * Calendar Embeds post type slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	const POSTTYPE = 'tec_calendar_embed';

	/**
	 * The meta key for storing the event categories.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	const META_KEY_CATEGORIES = 'event_categories';

	/**
	 * The meta key for storing the event tags.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	const META_KEY_TAGS = 'event_tags';

	/**
	 * Stores the hook suffix from `add_submenu_page`.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $hook_suffix;

	/**
	 * The post type object.
	 *
	 * @since TBD
	 *
	 * @var WP_Post_Type
	 */
	protected WP_Post_Type $post_type_object;

	/**
	 * Register custom post type for calendar embeds.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$labels = [
			'name'               => _x( 'Calendar Embeds', 'post type general name', 'the-events-calendar' ),
			'singular_name'      => _x( 'Calendar Embed', 'post type singular name', 'the-events-calendar' ),
			'menu_name'          => _x( 'Calendar Embeds', 'admin menu', 'the-events-calendar' ),
			'name_admin_bar'     => _x( 'Calendar Embed', 'add new on admin bar', 'the-events-calendar' ),
			'add_new'            => _x( 'Add New', 'calendar embed', 'the-events-calendar' ),
			'add_new_item'       => __( 'Add New Calendar Embed', 'the-events-calendar' ),
			'new_item'           => __( 'New Calendar Embed', 'the-events-calendar' ),
			'edit_item'          => __( 'Edit Calendar Embed', 'the-events-calendar' ),
			'view_item'          => __( 'View Calendar Embed', 'the-events-calendar' ),
			'all_items'          => __( 'Calendar Embeds', 'the-events-calendar' ),
			'search_items'       => __( 'Search Calendar Embeds', 'the-events-calendar' ),
			'parent_item_colon'  => __( 'Parent Calendar Embeds:', 'the-events-calendar' ),
			'not_found'          => __( 'No calendar embeds found.', 'the-events-calendar' ),
			'not_found_in_trash' => __( 'No calendar embeds found in Trash.', 'the-events-calendar' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'calendar-embed' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title' ],
			'show_in_rest'       => true,
		];

		/**
		 * Filter the arguments for the Calendar Embeds post type.
		 *
		 * @since TBD
		 *
		 * @param array $args The arguments for the Calendar Embeds post type.
		 *
		 * @return array
		 */
		$args = apply_filters( 'tec_events_calendar_embeds_post_type_args', $args );

		$this->post_type_object = register_post_type( static::POSTTYPE, $args );
	}

	/**
	 * Get the post type object.
	 *
	 * @since TBD
	 *
	 * @return WP_Post_Type
	 * @throws RuntimeException If the post type object is not set.
	 */
	public function get_post_type_object(): WP_Post_Type {
		if ( ! $this->post_type_object ) {
			throw new RuntimeException( __( 'Attempted to get post type object before it was set.', 'the-events-calendar' ) );
		}

		return $this->post_type_object;
	}
}
