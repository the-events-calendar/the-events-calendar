<?php
/**
 * Calendar Embeds Admin Page.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use Tribe__Events__Main as TEC;

/**
 * Class Page
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class Page {
	/**
	 * The menu hook suffix.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $hook_suffix;

	/**
	 * Register the Calendar Embeds menu item.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function register_menu_item(): string {
		$cpt               = get_post_type_object( TEC::POSTTYPE );
		$this->hook_suffix = add_submenu_page(
			'edit.php?post_type=' . TEC::POSTTYPE,
			esc_html( $this->get_page_title() ),
			esc_html( $this->get_menu_label() ),
			$cpt->cap->publish_posts,
			'edit.php?post_type=' . Calendar_Embeds::POSTTYPE,
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
	public function get_hook_suffix(): string {
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
	public function get_url(): string {
		return admin_url( 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE );
	}

	/**
	 * Gets the Menu label for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_menu_label(): string {
		return __( 'Embed Calendar', 'the-events-calendar' );
	}

	/**
	 * Gets the Page title for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_page_title(): string {
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
	public function keep_parent_menu_open( $submenu_file ): string {
		global $parent_file;

		if ( 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE !== $parent_file ) {
			return $submenu_file;
		}

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$parent_file = 'edit.php?post_type=' . TEC::POSTTYPE;

		return $submenu_file;
	}

	/**
	 * Check if the current screen is the Calendar Embeds page.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public static function is_on_page(): bool {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		return 'edit-' . Calendar_Embeds::POSTTYPE === $screen->id;
	}

	/**
	 * Register assets for the Calendar Embeds page.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_assets(): void {
		Asset::add(
			'tec-events-calendar-embeds-script',
			'js/admin/calendar-embeds-page.js'
		)
			->add_to_group_path( 'tec-events-resources' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ __CLASS__, 'is_on_page' ] )
			->set_dependencies( 'thickbox', 'tribe-clipboard' )
			->in_footer()
			->register();

		Asset::add(
			'tec-events-calendar-embeds-style',
			'css/admin/calendar-embeds-page.css'
		)
			->add_to_group_path( 'tec-events-resources' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ __CLASS__, 'is_on_page' ] )
			->set_dependencies( 'thickbox', 'tribe-common-admin' )
			->register();
	}
}
