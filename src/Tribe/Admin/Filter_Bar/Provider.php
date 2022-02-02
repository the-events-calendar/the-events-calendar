<?php
namespace Tribe\Events\Admin\Filter_Bar;

use Tribe__Events__Main;
use Tribe__Settings_Tab;
use Tribe__Admin__Helpers;


/**
 * Class Provider
 *
 * @since TBD
 *
 */
class Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		// Bail if Filter Bar is already installed.
		if ( class_exists( 'Tribe__Events__Filterbar__View' ) ) {
			return;
		}

		$this->add_actions();
		$this->add_assets();
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
	 * Register Assets.
	 * 
	 * @since TBD
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
	 * @since TBD
	 *
	 * @var Tribe__Template
	 */
	protected $template;

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
			$this->template->set_template_folder( 'src/admin-views/filter_bar' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
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
		
		return $this->get_template()->template( 'upsell', wp_parse_args( $context ), $echo );
	}

	/**
	 * Create a Filter Bar upsell tab.
	 * 
	 * @since TBD
	 */
	public function add_tab() {

		$tec_events_filter_bar_upsell_tab = [
			'filter_bar-upsell-info-box-description' => [
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
		$tec_events_admin_filter_bar_upsell_fields = apply_filters( 'tec_events_filterbar_upgrade_content', $tec_events_filter_bar_upsell_tab );
		
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