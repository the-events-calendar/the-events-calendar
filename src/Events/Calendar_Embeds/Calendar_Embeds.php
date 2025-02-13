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
	 * The page slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $slug = 'calendar-embeds';

	/**
	 * Stores the Registered ID from `add_submenu_page`.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $ID;

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

		register_post_type( static::POSTTYPE, $args );
	}

	/**
	 * Register the Calendar Embeds menu item.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function register_menu_item() {
		$cpt      = get_post_type_object( TEC::POSTTYPE );
		$this->ID = add_submenu_page(
			'edit.php?post_type=' . TEC::POSTTYPE,
			esc_html( $this->get_page_title() ),
			esc_html( $this->get_menu_label() ),
			$cpt->cap->publish_posts,
			'edit.php?post_type=' . self::POSTTYPE,
		);

		return $this->ID;
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

		$parent_file = 'edit.php?post_type=' . TEC::POSTTYPE;

		return $submenu_file;
	}
}
