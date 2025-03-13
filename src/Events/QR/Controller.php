<?php
/**
 * The Controller class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller.
 *
 * @since   TBD
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
	private $shortcode_tag = 'tec_event_qr';

	/**
	 * Register the controller.
	 *
	 * @since   TBD
	 *
	 * @uses  Notices::register_admin_notices()
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->container->singleton( Settings::class );
		$this->container->singleton( Shortcode::class );

		$this->add_actions();

		$this->register_assets();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since   TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_actions();
	}

	/**
	 * Adds the actions required by the controller.
	 *
	 * @since 5.7.0
	 *
	 * @return void
	 */
	protected function add_actions(): void {
		add_action( 'init', [ $this, 'register_shortcode' ] );

		add_filter( 'tec_qr_notice_valid_pages', [ $this, 'add_valid_pages' ] );
	}

	/**
	 * Removes the actions required by the controller.
	 *
	 * @since 5.7.0
	 *
	 * @return void
	 */
	protected function remove_actions(): void {
		remove_action( 'init', [ $this, 'register_shortcode' ] );

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
	 * @since 5.7.0
	 *
	 * @return void
	 */
	protected function register_assets(): void {
		// @TODO load our QR CSS and JS here using tribe_asset()
	}


	/**
	 * Gets the shortcode tag.
	 *
	 * @since TBD
	 *
	 * @return string The shortcode tag.
	 */
	public function get_shortcode_tag(): string {
		return $this->shortcode_tag;
	}

	/**
	 * This will be called at hook "init" to allow other plugins and themes to hook to shortcode easily
	 *
	 * @since TBD
	 * @return void
	 */
	public function register_shortcode() {
		$tag = $this->get_shortcode_tag();

		add_shortcode( $tag, [ $this->container->make( Shortcode::class ), 'render' ] );
	}
}
