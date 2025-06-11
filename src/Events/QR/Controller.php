<?php
/**
 * The Controller class for the QR module.
 *
 * @since 6.12.0
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\QR\Routes;
use Tribe__Events__Main as TEC;

/**
 * Class Controller.
 *
 * @since 6.12.0
 *
 * @package TEC\Events\QR
 *
 * @property \TEC\Common\Contracts\Provider\Container $container
 */
class Controller extends Controller_Contract {
	/**
	 * The QR code slug.
	 *
	 * @since 6.12.0
	 * @var string
	 */
	public const QR_SLUG = 'tec_event_qr';

	/**
	 * The QR code instance.
	 *
	 * @since 6.12.0
	 * @var QR_Code
	 */
	private $qr_code;

	/**
	 * Register the controller.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function do_register(): void {
		$this->container->register( Routes::class );
		$this->container->register( Redirections::class );

		$this->qr_code = $this->container->make( QR_Code::class );

		$this->add_hooks();

		$this->register_assets();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_hooks();
	}

	/**
	 * Adds the actions required by the controller.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	protected function add_hooks(): void {
		add_filter( 'tribe_shortcodes', [ $this, 'filter_register_shortcodes' ] );
		add_filter( 'post_row_actions', [ $this->qr_code, 'add_admin_table_action' ], 10, 2 );
		add_filter( 'tec_qr_notice_valid_pages', [ $this, 'add_valid_pages' ] );
		add_action( 'wp_ajax_tec_qr_code_modal', [ $this->qr_code, 'render_modal' ] );
		add_action( 'add_meta_boxes', [ $this->qr_code, 'add_qr_code_meta_box' ] );
	}

	/**
	 * Removes the actions required by the controller.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	protected function remove_hooks(): void {
		remove_filter( 'tribe_shortcodes', [ $this, 'filter_register_shortcodes' ] );
		remove_filter( 'post_row_actions', [ $this->qr_code, 'add_admin_table_action' ] );
		remove_filter( 'tec_qr_notice_valid_pages', [ $this, 'add_valid_pages' ] );
		remove_action( 'wp_ajax_tec_qr_code_modal', [ $this->qr_code, 'render_modal' ] );
		remove_action( 'add_meta_boxes', [ $this->qr_code, 'add_qr_code_meta_box' ] );
	}

	/**
	 * Adds the TEC pages to the list for the QR code notice.
	 *
	 * @since 6.12.0
	 * @param array $valid_pages An array of pages where notice will be displayed.
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
	 * Checks if the QR module is active.
	 *
	 * @since 6.12.0
	 * @return bool Whether the QR module is active.
	 */
	public function is_active(): bool {
		/**
		 * Filter whether QR functionality is enabled.
		 *
		 * @since 6.12.0
		 *
		 * @param bool $enabled Whether QR functionality is enabled.
		 */
		return (bool) apply_filters( 'tec_events_qr_enabled', true );
	}

	/**
	 * Register the assets related to the QR module.
	 *
	 * @since 6.12.0
	 * @return void
	 */
	protected function register_assets(): void {
		tec_asset(
			TEC::instance(),
			'tec-events-qr-code-styles',
			'qr-code.css',
			[ 'wp-components' ],
			'admin_enqueue_scripts',
			[ 'conditionals' => [ $this, 'should_enqueue_assets' ] ]
		);
	}

	/**
	 * If we should enqueue assets.
	 *
	 * @since 6.12.0
	 * @return bool Whether we should enqueue assets.
	 */
	public function should_enqueue_assets(): bool {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		$valid_screens = [ 'edit-' . TEC::POSTTYPE, TEC::POSTTYPE ];
		/**
		 * Filters the list of valid screen IDs where QR code assets should be enqueued.
		 *
		 * @since 6.12.0
		 *
		 * @param array $valid_screens Array of screen IDs where QR code assets should be loaded.
		 *                             Default: ['edit-tribe_events', 'tribe_events']
		 */
		$valid_screens = apply_filters( 'tec_events_qr_valid_screens', $valid_screens );

		return in_array( $screen->id, $valid_screens, true );
	}

	/**
	 * Register shortcodes.
	 *
	 * @see   \Tribe\Shortcode\Manager::get_registered_shortcodes()
	 * @since 6.12.0
	 * @param array $shortcodes An associative array of shortcodes in the shape `[ <slug> => <class> ]`.
	 * @return array
	 */
	public function filter_register_shortcodes( array $shortcodes ) {
		// Check if QR is enabled.
		if ( ! $this->is_active() ) {
			return $shortcodes;
		}

		$shortcodes[ static::QR_SLUG ] = Shortcode::class;

		return $shortcodes;
	}
}
