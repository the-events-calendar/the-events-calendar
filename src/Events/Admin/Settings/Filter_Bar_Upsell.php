<?php
namespace TEC\Events\Admin\Settings;

use Tribe\Events\Admin\Settings;
use Tribe__Settings_Tab;
use TEC\Common\Contracts\Service_Provider;



/**
 * Class Upsell
 *
 * @since TBD
 *
 */
class Filter_Bar_Upsell extends Service_Provider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
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
	}

	/**
	 * Add actions.
	 *
	 * @since TBD
	 */
	public function add_actions() {
		add_action( 'tribe_settings_do_tabs', [ $this, 'add_tab' ] );
	}

	/**
	 * Stores the instance of the template engine that we will use for rendering the elements.
	 *
	 * @since TBD
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Create a Filter Bar upsell tab.
	 *
	 * @since TBD
	 */
	public function add_tab( $admin_page ) {
		$tec_settings_page_id = tribe( Settings::class )::$settings_page_id;

		if ( ! empty( $admin_page ) && $tec_settings_page_id !== $admin_page ) {
			return;
		}

		$tec_events_filter_bar_upsell_tab = [
			'filter_bar-upsell-info-box' => [
				'type' => 'html',
				'html' => $this->get_upsell_html(),
			],
		];

		/**
		* Allows the fields displayed in the Filter Bar upsell tab to be modified.
		*
		* @since TBD
		*
		* @param array $tec_events_filter_bar_upsell_tab Array of fields used to setup the Filter Bar upsell Tab.
		*/
		$tec_events_admin_filter_bar_upsell_fields = apply_filters(
			'tec_events_settings_filterbar_tab_content',
			$tec_events_filter_bar_upsell_tab
		);

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
	 * Returns html of the Filter Bar upsell banner.
	 *
	 * @since TBD
	 *
	 * @param array   $context Context of template.
	 * @param boolean $echo    Whether or not to output the HTML or just return it.
	 *
	 * @return Tribe__Template
	 */
	public function get_upsell_html( $context = [], $echo = false ) {
		return $this->get_template()->template( 'filter_bar', wp_parse_args( $context ), $echo );
	}

	/**
	 * Gets the template instance used to setup the rendering html.
	 *
	 * @since TBD
	 *
	 * @return Tribe__Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new \Tribe__Template();
			$this->template->set_template_origin( \Tribe__Events__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/settings/upsells/' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}