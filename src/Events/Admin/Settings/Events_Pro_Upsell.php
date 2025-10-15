<?php
/**
 * Service Provider for Events Calendar Pro upsell/settings.
 *
 * @since TBD
 *
 * @package TEC\Events\Admin\Settings
 */

namespace TEC\Events\Admin\Settings;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Template;

/**
 * Class Events_Pro_Upsell
 *
 * @since TBD
 */
class Events_Pro_Upsell extends Service_Provider {

	/**
	 * The slug of the upsell tab.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $slug = 'events-pro';

	/**
	 * Stores the instance of the template engine that we will use for rendering the elements.
	 *
	 * @since TBD
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register(): void {
		if ( tec_should_hide_upsell() ) {
			return;
		}

		// Bail if Events Calendar Pro is already installed/registered.
		if ( has_action( 'tribe_common_loaded', 'tribe_register_pro' ) ) {
			return;
		}

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add actions.
	 *
	 * @since TBD
	 */
	public function add_actions(): void {
		add_action( 'tribe_events_date_display', [ $this, 'render_recurrence_banner' ], 18 );
	}

	/**
	 * Add filters.
	 *
	 * @since TBD
	 */
	public function add_filters(): void {
	}

	/**
	 * Render the Recurrence banner inside Classic Editor.
	 *
	 * @since TBD
	 */
	public function render_recurrence_banner(): void {
		$this->get_template()->template( 'recurrence-banner' );
	}

	/**
	 * Gets the template instance used to setup the rendering html.
	 *
	 * @since TBD
	 *
	 * @return Tribe__Template
	 */
	public function get_template(): Tribe__Template {
		if ( empty( $this->template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( \Tribe__Events__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/settings/upsells/' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}
}
