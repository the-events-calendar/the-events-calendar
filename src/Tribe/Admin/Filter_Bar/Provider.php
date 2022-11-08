<?php
namespace Tribe\Events\Admin\Filter_Bar;

use Tribe\Events\Admin\Settings;
use Tribe__Events__Main;
use Tribe__Settings_Tab;
use Tribe__Admin__Helpers;


/**
 * Class Provider
 *
 * @since 5.14.0
 *
 */
class Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 5.14.0
	 */
	public function register() {
		if ( tec_should_hide_upsell() ) {
			return;
		}


		// Bail if Filter Bar is already installed/registered.
		if ( has_action( 'tribe_common_loaded', 'tribe_register_filterbar' ) ) {
			return;
		}

		$this->add_actions();
		$this->add_assets();
	}

	/**
	 * Add actions.
	 *
	 * @since 5.14.0
	 */
	public function add_actions() {
		add_action( 'tribe_settings_do_tabs', [ $this, 'add_tab' ] );
	}

	/**
	 * Register Assets.
	 *
	 * @since 5.14.0
	 */
	public function add_assets() {
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
	}

	/**
	 * Stores the instance of the template engine that we will use for rendering the elements.
	 *
	 * @since 5.14.0
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Gets the template instance used to setup the rendering html.
	 *
	 * @since 5.14.0
	 *
	 * @return Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( \Tribe__Events__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/filter_bar' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * Returns html of the Filter Bar upsell banner.
	 *
	 * @since 5.14.0
	 *
	 * @param array   $context Context of template.
	 * @param boolean $echo    Whether or not to output the HTML or just return it.
	 *
	 * @return Tribe__Template
	 */
	public function get_upsell_html( $context = [], $echo = false ) {

		return $this->get_template()->template( 'upsell', wp_parse_args( $context ), $echo );
	}

	/**
	 * Create a Filter Bar upsell tab.
	 *
	 * @since 5.14.0
	 * @since 5.15.0 Early bail if we're not on TEC settings.
	 */
	public function add_tab( $admin_page ) {
		$tec_settings_page_id = tribe( Settings::class )::$settings_page_id;

		if ( ! empty( $admin_page ) && $tec_settings_page_id !== $admin_page ) {
			return;
		}

		$tec_events_filter_bar_upsell_tab = [
			'filter_bar-upsell-info-box-description' => [
				'type' => 'html',
				'html' => $this->get_upsell_html(),
			],
		];

		/**
		* Allows the fields displayed in the Filter Bar upsell tab to be modified.
		*
		* @since 5.14.0
		*
		* @param array $tec_events_filter_bar_upsell_tab Array of fields used to setup the Filter Bar upsell Tab.
		*/
		$tec_events_admin_filter_bar_upsell_fields = apply_filters( 'tec_events_filterbar_upgrade_content', $tec_events_filter_bar_upsell_tab );

		new Tribe__Settings_Tab(
			'filter-view',
			esc_html_x( 'Filters', 'Label for the Filters tab.', 'the-events-calendar' ),
			[
				'priority'      => 40,
				'fields'        => $tec_events_admin_filter_bar_upsell_fields,
				'network_admin' => is_network_admin(),
				'show_save'     => false,
			]
		);

		add_filter(
			'tec_events_settings_tabs_ids',
			function( $tabs ) {
				$tabs[] = 'filter-view';
				return $tabs;
			}
		);
	}

	/**
	 * Checks whether we are on the correct admin page to enqueue admin.
	 *
	 * @since 5.14.0
	 *
	 * @return bool
	 */
	public function should_enqueue_admin() {
		return Tribe__Admin__Helpers::instance()->is_screen();
	}
}
