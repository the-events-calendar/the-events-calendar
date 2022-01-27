<?php
namespace Tribe\Events\Admin\Filter_Bar;

use Tribe__Events__Main;
use Tribe__Settings_Tab;
use Tribe__Admin__Helpers;

// Bail if Filter Bar is already installed.
if ( ! class_exists( 'Tribe__Events__Filterbar__View' ) ) {
	/**
	 * Class Provider
	 *
	 * @since TBD
	 *
	 */
	class Provider {
		/**
		 * Add filters and actions.
		 *
		 * @since TBD
		 */
		public function hook() {
			add_action( 'tribe_settings_do_tabs', [ $this, 'do_filter_bar_upsell_tab' ] );
		}

		/**
		 * Create a Filter Bar upsell tab.
		 * 
		 * @since TBD
		 */
		public function do_filter_bar_upsell_tab() {

			tribe_asset(
				Tribe__Events__Main::instance(),
				'tec-admin-filterbar-upsell',
				'tec-admin-filterbar-upsell.css',
				[],
				'admin_enqueue_scripts',
				[
					'conditionals' => [ $this, 'should_enqueue_admin' ],
				]
			);

			ob_start();
			include_once Tribe__Events__Main::instance()->plugin_path . 'src/admin-views/filterbar/banners/filterbar-upsell.php';
			$tec_events_filter_bar_upsell_tab_html = ob_get_clean();

			$tec_events_filter_bar_upsell_tab = [
				'filter_bar-upsell-info-box-description' => [
					'type' => 'html',
					'html' => $tec_events_filter_bar_upsell_tab_html,
				],
			];
			
			/**
			* Allows the fields displayed in the Filter Bar upsell tab to be modified.
			*
			* @since TBD
			*
			* @param array $tec_events_filter_bar_upsell_tab Array of fields used to setup the Filter Bar upsell Tab.
			*/
			$tec_events_admin_filter_bar_upsell_fields = apply_filters( 'tribe_upgrade_fields', $tec_events_filter_bar_upsell_tab );
			
			new Tribe__Settings_Tab(
				'filter-view', esc_html__( 'Filters', 'the_events_calendar' ),
				[
					'priority'      => 40,
					'fields'        => $tec_events_admin_filter_bar_upsell_fields,
					'network_admin' => is_network_admin(),
					'show_save'     => false,
				]
			);
		}

		/**
		 * Checks whether we are on the correct admin page to enqueue admin.
		 *
		 * @since TBD
		 *
		 * @return bool
		 */
		public function should_enqueue_admin() {
			return Tribe__Admin__Helpers::instance()->is_screen();
		}
	}
}