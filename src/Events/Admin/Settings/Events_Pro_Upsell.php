<?php
/**
 * Service Provider for Events Calendar Pro upsell/settings.
 *
 * @since 6.15.9
 *
 * @package TEC\Events\Admin\Settings
 */

namespace TEC\Events\Admin\Settings;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Template;
use TEC\Common\Admin\Conditional_Content\Traits\Is_Dismissible;

/**
 * Class Events_Pro_Upsell
 *
 * @since 6.15.9
 */
class Events_Pro_Upsell extends Service_Provider {
	use Is_Dismissible;

	/**
	 * The slug of the upsell tab.
	 *
	 * @since 6.15.9
	 *
	 * @var string
	 */
	protected string $slug = 'recurrence-upsell';

	/**
	 * Stores the instance of the template engine that we will use for rendering the elements.
	 *
	 * @since 6.15.9
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Get the slug used for dismissal and identification.
	 *
	 * @since 6.15.9
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.15.9
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
	 * @since 6.15.9
	 */
	public function add_actions(): void {
		add_action( 'tribe_events_date_display', [ $this, 'render_recurrence_banner' ], 18 );
		// AJAX dismiss handler.
		add_action( 'wp_ajax_tec_conditional_content_dismiss', [ $this, 'handle_dismiss' ] );
	}

	/**
	 * Add filters.
	 *
	 * @since 6.15.9
	 */
	public function add_filters(): void {
	}

	/**
	 * Render the Recurrence banner inside Classic Editor.
	 *
	 * @since 6.15.9
	 */
	public function render_recurrence_banner(): void {
		// Do not render if user already dismissed.
		if ( $this->has_user_dismissed() ) {
			return;
		}

		// Ensure conditional-content JS is available.
		do_action( 'tec_conditional_content_assets' );

		$this->get_template()->template(
			'recurrence-banner',
			[
				'nonce' => $this->get_nonce(),
				'slug'  => $this->get_slug(),
			]
		);
	}

	/**
	 * Gets the template instance used to setup the rendering html.
	 *
	 * @since 6.15.9
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
