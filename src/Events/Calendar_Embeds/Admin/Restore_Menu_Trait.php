<?php
/**
 * Trait Restore_Menu_Trait
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

/**
 * Trait Restore_Menu_Trait
 *
 * @since 6.11.0
 */
trait Restore_Menu_Trait {

	/**
	 * The stored globals.
	 *
	 * @since 6.11.0
	 *
	 * @var array
	 */
	protected static array $stored_globals = [
		'parent_file'  => '',
		'submenu_file' => '',
	];

	/**
	 * Restores the current parent file.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function restore_menu_globals(): void {
		if ( empty( self::$stored_globals ) ) {
			return;
		}

		global $parent_file, $submenu_file;

		if ( ! empty( self::$stored_globals['parent_file'] ) ) {
			$parent_file = self::$stored_globals['parent_file']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		if ( ! empty( self::$stored_globals['submenu_file'] ) ) {
			$submenu_file = self::$stored_globals['submenu_file']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
