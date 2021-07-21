<?php
/**
 * Handles notices having to do with Customizer.
 *
 * TBD
 *
 * @package Tribe\Events\Views\V2\Customizer
 */

namespace Tribe\Events\Views\V2\Customizer;


/**
 * Class Notice
 *
 * @since TBD
 *
 * @package Tribe\Events\Views\V2\Customizer
 */
class Notice {
	/**
	 * Extension hooks and initialization; exits if the extension is not authorized by Tribe Common to run.
	 *
	 * @since  TBD
	 */
	public function hook() {
		tribe_notice(
			'customizer_font_size_extension',
			[ $this, 'display_notice' ],
			[
				'type'     => 'warning',
				'dismiss'  => 1,
				'priority' => 0,
				'wrap'     => 'p',
			],
			[ $this, 'should_display' ]
		);
	}

	/**
	 * Checks if we have the TEC Inherit Theme Fonts Extension active
	 *
	 * @since  TBD
	 *
	 * @return boolean
	 */
	public function should_display() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		$current_screen = get_current_screen();

		$screens = [
			'customize', // Customizer
			'tribe_events_page_tribe-app-shop', // App shop.
			'events_page_tribe-app-shop', // App shop.
			'tribe_events_page_tribe-common', // Settings & Welcome.
			'events_page_tribe-common', // Settings & Welcome.
			'toplevel_page_tribe-common', // Settings & Welcome.
		];

		// If not a valid screen, don't display.
		if ( empty( $current_screen->id ) || ! in_array( $current_screen->id, $screens, true ) ) {
			return false;
		}

		return class_exists( 'Tribe\Extensions\InheritThemeFonts\Main' );
	}

	/**
	 * HTML for the notice.
	 *
	 * @since  TBD
	 *
	 * @return string
	 */
	public function display_notice() {
		$path = 'plugins.php#deactivate-the-events-calendar-extension-inherit-theme-fonts';

		return sprintf(
			__(
				'You are using the Inherit Theme Fonts extension. Font control is now built into <a href="%1$s" target="_blank">%2$s</a> Please disable the Inherit Theme Fonts extension to prevent conflicts with The Events Calendar.',
				'the-events-calendar'
			),
			'https://evnt.is/1ast',
			esc_html__( "The Events Calendar's options in the WordPress Customizer.", 'the-events-calendar' )
		);
	}
}
