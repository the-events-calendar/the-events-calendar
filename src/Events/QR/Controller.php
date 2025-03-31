<?php
/**
 * The Controller class for the QR module.
 *
 * @since TBD
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\QR\Routes;
use TEC\Common\lucatume\DI52\Container;
use Tribe__Events__Main as TEC;

/**
 * Class Controller.
 *
 * @since TBD
 *
 * @package TEC\Events\QR
 *
 * @property \TEC\Common\Contracts\Provider\Container $container
 */
class Controller extends Controller_Contract {

	/**
	 * The shortcode tag.
	 *
	 * @since TBD
	 * @var string
	 */
	private $slug;

	/**
	 * The QR code instance.
	 *
	 * @since TBD
	 * @var QR_Code
	 */
	private $qr_code;

	/**
	 * Controller constructor.
	 *
	 * @since TBD
	 *
	 * @param Container $container   The DI container.
	 * @param QR_Code   $qr_code     The QR code instance.
	 */
	public function __construct( Container $container, QR_Code $qr_code ) {
		parent::__construct( $container );
		$this->qr_code = $qr_code;
	}

	/**
	 * Register the controller.
	 *
	 * @since TBD
	 * @return void
	 */
	public function do_register(): void {
		$this->container->singleton( Settings::class );
		$this->container->register( Routes::class );
		$this->container->register( Redirections::class );

		$this->slug = Settings::get_qr_slug();

		$this->add_hooks();

		$this->register_assets();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 * @return void
	 */
	public function unregister(): void {
		$this->remove_hooks();
	}

	/**
	 * Adds the actions required by the controller.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * Register the assets related to the QR module.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function register_assets(): void {
		tribe_asset(
			TEC::instance(),
			'tec-events-qr-code-styles',
			'qr-code.css',
			[ 'wp-components' ],
			'admin_enqueue_scripts',
			[ 'conditionals' => [ $this, 'should_enqueue_assets' ] ]
		);
	}

	/**
	 * Gets the shortcode slug.
	 *
	 * @since TBD
	 * @return string The shortcode slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * If we should enqueue assets.
	 *
	 * @since TBD
	 * @return bool Whether we should enqueue assets.
	 */
	public function should_enqueue_assets(): bool {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		$valid_screens = [ 'edit-tribe_events', 'tribe_events' ];
		/**
		 * Filters the list of valid screen IDs where QR code assets should be enqueued.
		 *
		 * @since TBD
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
	 * @since TBD
	 * @param array $shortcodes An associative array of shortcodes in the shape `[ <slug> => <class> ]`.
	 * @return array
	 */
	public function filter_register_shortcodes( array $shortcodes ) {
		// Check if QR is enabled.
		if ( ! $this->qr_code->is_enabled() ) {
			return $shortcodes;
		}

		$shortcodes[ Settings::get_qr_slug() ] = Shortcode::class;

		return $shortcodes;
	}
}
