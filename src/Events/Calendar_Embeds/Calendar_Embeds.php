<?php
/**
 * Manages the External Calendar Embeds Feature.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use Tribe__Events__Main as TEC;

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
	 * Stores the hook suffix from `add_submenu_page`.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $hook_suffix;

	/**
	 * The post type object.
	 *
	 * @since TBD
	 *
	 * @var \WP_Post_Type
	 */
	protected $post_type_object;

	/**
	 * Register custom post type for calendar embeds.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_post_type() {
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
	 * @return \WP_Post_Type
	 * @throws \RuntimeException If the post type object is not set.
	 */
	public function get_post_type_object() {
		if ( ! $this->post_type_object ) {
			throw new \RuntimeException( __( 'Attempted to get post type object before it was set.', 'the-events-calendar' ) );
		}

		return $this->post_type_object;
	}

	/**
	 * Register the Calendar Embeds menu item.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function register_menu_item() {
		$cpt               = get_post_type_object( TEC::POSTTYPE );
		$this->hook_suffix = add_submenu_page(
			'edit.php?post_type=' . TEC::POSTTYPE,
			esc_html( $this->get_page_title() ),
			esc_html( $this->get_menu_label() ),
			$cpt->cap->publish_posts,
			'edit.php?post_type=' . self::POSTTYPE,
		);

		return $this->hook_suffix;
	}

	/**
	 * Gets the hook suffix for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 * @throws \RuntimeException If the hook suffix is not set.
	 */
	public function get_hook_suffix() {
		if ( ! $this->hook_suffix ) {
			throw new \RuntimeException( __( 'Attempted to get hook suffix before it was set.', 'the-events-calendar' ) );
		}

		return $this->hook_suffix;
	}

	/**
	 * Gets the URL for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_url() {
		return admin_url( 'edit.php?post_type=' . self::POSTTYPE );
	}

	/**
	 * Gets the Menu label for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_menu_label() {
		return __( 'Embed Calendar', 'the-events-calendar' );
	}

	/**
	 * Gets the Page title for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_page_title() {
		return __( 'Embed Calendar', 'the-events-calendar' );
	}

	/**
	 * Keep parent menu open when adding and editing calendar embeds.
	 *
	 * @since TBD
	 *
	 * @param string $submenu_file The current submenu file.
	 *
	 * @return string
	 */
	public function keep_parent_menu_open( $submenu_file ) {
		global $parent_file;

		if ( 'edit.php?post_type=' . self::POSTTYPE !== $parent_file ) {
			return $submenu_file;
		}

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$parent_file = 'edit.php?post_type=' . TEC::POSTTYPE;

		return $submenu_file;
	}
}
