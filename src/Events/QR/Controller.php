<?php
/**
 * The Controller class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\QR\Shortcode;

/**
 * Class Controller.
 *
 * @since TBD
 *
 * @package TEC\Events\QR
 */
class Controller extends Controller_Contract {

	/**
	 * The shortcode tag.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private $slug = 'tec_event_qr';

	/**
	 * Register the controller.
	 *
	 * @since TBD
	 *
	 * @uses  Notices::register_admin_notices()
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->container->singleton( Settings::class );

		$this->add_hooks();

		$this->register_assets();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_hooks();
	}

	/**
	 * Adds the actions required by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function add_hooks(): void {
		add_filter( 'tribe_shortcodes', [ $this, 'filter_register_shortcodes' ] );

		add_filter( 'tec_qr_notice_valid_pages', [ $this, 'add_valid_pages' ] );
	}

	/**
	 * Removes the actions required by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function remove_hooks(): void {
		remove_filter( 'tribe_shortcodes', [ $this, 'filter_register_shortcodes' ] );

		remove_filter( 'tec_qr_notice_valid_pages', [ $this, 'add_valid_pages' ] );
	}

	/**
	 * Adds the TEC pages to the list for the QR code notice.
	 *
	 * @since TBD
	 *
	 * @param array $valid_pages An array of pages where notice will be displayed.
	 *
	 * @return array
	 */
	public function add_valid_pages( $valid_pages ) {
		$tec_pages = [
			'tec-events-settings',
			'tec-events-help-hub',
			'tec-troubleshooting',
		];

		return array_merge( $valid_pages, $tec_pages );
	}

	/**
	 * Register the assets related to the QR module.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function register_assets(): void {
		// @TODO load our QR CSS and JS here using TEC\Common\StellarWP\Asset
	}

	/**
	 * Gets the shortcode slug.
	 *
	 * @since TBD
	 *
	 * @return string The shortcode slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Register shortcodes.
	 *
	 * @see   \Tribe\Shortcode\Manager::get_registered_shortcodes()
	 *
	 * @since TBD
	 *
	 * @param array $shortcodes An associative array of shortcodes in the shape `[ <slug> => <class> ]`.
	 *
	 * @return array
	 */
	public function filter_register_shortcodes( array $shortcodes ) {
		// Check if QR is enabled.
		$slugs   = Settings::get_option_slugs();
		$enabled = tribe_get_option( $slugs['enabled'], false );

		if ( ! $enabled ) {
			return $shortcodes;
		}

		$shortcodes[ $this->get_slug() ] = Shortcode::class;

		return $shortcodes;
	}
}
