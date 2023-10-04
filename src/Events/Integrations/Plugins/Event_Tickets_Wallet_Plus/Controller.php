<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets_Wallet_Plus;

use TEC\Common\Integrations\Traits\Plugin_Integration;
use TEC\Events\Integrations\Integration_Abstract;
use Tribe__Template;

/**
 * Class Controller
 *
 * @since   6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Controller extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * Apple Wallet class.
	 *
	 * @since TBD
	 *
	 * @var Apple_Wallet
	 */
	protected Apple_Wallet $apple_wallet_class;

	/**
	 * Pdf class.
	 *
	 * @since TBD
	 *
	 * @var Pdf
	 */
	protected Pdf $pdf_class;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'event-tickets-wallet-plus';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {

		return true;

		// @todo @codingmusician: Fix the logic below and uncomment.
		// if ( ! class_exists( '\TEC\Tickets_Wallet_Plus\Controller', false ) ) {
		// 	return false;
		// }

		// return tribe( '\TEC\Tickets_Wallet_Plus\Controller' )->is_active();
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		$this->register_actions();
		$this->register_filters();
	}

	/**
	 * Register actions.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_actions() {
		add_action( 'tribe_template_after_include:tickets-wallet-plus/pdf/pass/styles', [ $this, 'add_styles_to_pdf' ], 10, 3 );
		add_action( 'tribe_template_before_include:tickets-wallet-plus/pdf/pass/body/sidebar', [ $this, 'maybe_add_venue_to_pdf' ], 10, 3 );
	}

	/**
	 * Register filters.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_filters() {
		add_filter( 'tec_tickets_wallet_plus_pdf_pass_template_vars', [ $this, 'filter_pdf_template_vars' ] );
	}

	/**
	 * Add styles to PDF.
	 *
	 * @since TBD
	 *
	 * @param string          $file     Path to the file.
	 * @param string          $name     Name of the file.
	 * @param Tribe__Template $template Template instance.
	 *
	 * @return void
	 */
	public function add_styles_to_pdf( $file, $name, $template ) {
		$this->container->make( Pdf::class )->add_tec_styles_to_pdf( $file, $name, $template );
	}

	/**
	 * Maybe add venue to PDF.
	 *
	 * @since TBD
	 *
	 * @param string          $file     Path to the file.
	 * @param string          $name     Name of the file.
	 * @param Tribe__Template $template Template instance.
	 *
	 * @return void
	 */
	public function maybe_add_venue_to_pdf( $file, $name, $template ) {
		$this->container->make( Pdf::class )->maybe_add_venue_to_pdf( $file, $name, $template );
	}

	/**
	 * Filter PDF template vars.
	 *
	 * @since TBD
	 *
	 * @param array $vars Template vars.
	 *
	 * @return array
	 */
	public function filter_pdf_template_vars( $vars ): array {
		return $this->container->make( Pdf::class )->filter_pdf_template_vars( $vars );
	}
}
