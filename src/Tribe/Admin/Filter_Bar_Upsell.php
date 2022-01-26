<?php
namespace Tribe\Events\Admin;

use Tribe__Events__Main;
use Tribe__Settings_Tab;

/**
 * Class Filter_Bar_Upsell
 *
 * @since TBD
 *
 */
class Filter_Bar_Upsell {
	/**
	 * Add filters and actions.
	 *
	 * @since TBD
	 */
	public function hook() {
		add_action( 'tribe_settings_do_tabs', [ $this, 'do_fbar_upsell_tab' ] );
	}

	/**
	 * Create a Filter Bar upsell tab.
	 * 
	 * @since TBD
	 */
	public function do_fbar_upsell_tab() {
		// Bail if Filter Bar is already installed.
		if ( class_exists( 'Tribe__Events__Filterbar__View' ) ) {
			return;
		}

		tribe_asset(
			Tribe__Events__Main::instance(),
			'tec-admin-filterbar-upsell',
			'tec-admin-filterbar-upsell.css',
			[],
			'admin_enqueue_scripts',
		);

		ob_start();
		include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/filterbar/banners/filterbar-upsell.php';
		$tec_events_fbar_upsell_tab_html = ob_get_clean();

		$tec_events_fbar_upsell_tab = [
			'info-box-description' => [
				'type' => 'html',
				'html' => $tec_events_fbar_upsell_tab_html,
			],
		];
		
		/**
		* Allows the fields displayed in the Filter Bar upsell tab to be modified.
		*
		* @since TBD
		*
		* @param array $tec_events_fbar_upsell_tab Array of fields used to setup the Filter Bar upsell Tab.
		*/
		$tec_events_fbar_upsell_fields = apply_filters( 'tribe_upgrade_fields', $tec_events_fbar_upsell_tab );
		
		new Tribe__Settings_Tab(
			'filter-view', esc_html__( 'Filters', 'the_events_calendar' ),
			[
				'priority'      => 40,
				'fields'        => $tec_events_fbar_upsell_fields,
				'network_admin' => is_network_admin(),
				'show_save'     => false,
			]
		);
	}
}