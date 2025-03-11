<?php
/**
 * The Controller class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Main as Common;

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
		$this->container->bind( QR::class, [ $this, 'bind_facade_or_error' ] );
		$this->container->singleton( Settings::class );
		$this->container->singleton( Notices::class );
		$this->container->singleton( Shortcode::class );

		// Register the Admin Notices right away.
		$this->container->make( Notices::class )->register_admin_notices();

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
	}

	/**
	 * Register the assets related to the QR module.
	 *
	 * @since 5.7.0
	 *
	 * @return void
	 */
	protected function register_assets(): void {
		// @ToDo load our QR CSS and JS here using tribe_asset()
	}

	/**
	 * Binds the facade or throws an error.
	 *
	 * @since TBD
	 *
	 * @return \WP_Error|QR Either the build QR façade, or an error to detail the failure.
	 */
	public function bind_facade_or_error() {
		if ( ! $this->can_use() ) {
			return new \WP_Error(
				'tec_events_qr_code_cannot_use',
				__( 'The QR code cannot be used, please contact your host and ask for `gzip` and `gd` support.', 'the-events-calendar' )
			);
		}

		// Load the library if it's not loaded already.
		$this->load_library();

		return new QR();
	}

	/**
	 * Determines if the QR code library is loaded.
	 *
	 * @since TBD
	 */
	public function has_library_loaded(): bool {
		return defined( 'TEC_COMMON_QR_CACHEABLE' );
	}

	/**
	 * Loads the QR code library if it's not loaded already.
	 *
	 * @since TBD
	 */
	protected function load_library(): void {
		if ( $this->has_library_loaded() ) {
			return;
		}

		require_once Common::instance()->plugin_path . 'vendor/phpqrcode/qrlib.php';
	}

	/**
	 * Determines if the QR code can be used.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the current server configuration supports the QR functionality.
	 */
	public function can_use(): bool {
		$can_use = function_exists( 'gzuncompress' ) && function_exists( 'ImageCreate' );

		/**
		 * Filter to determine if the QR code can be used.
		 *
		 * @since TBD
		 *
		 * @param bool $can_use Whether the QR code can be used based on the current environment.
		 */
		return apply_filters( 'tec_events_qr_code_can_use', $can_use );
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
