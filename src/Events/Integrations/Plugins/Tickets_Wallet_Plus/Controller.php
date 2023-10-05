<?php

namespace TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus;

use TEC\Common\Integrations\Traits\Plugin_Integration;
use TEC\Events\Integrations\Integration_Abstract;
use Tribe__Template;

/**
 * Class Controller
 *
 * @since   6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Tickets_Wallet_Plus
 */
class Controller extends Integration_Abstract {
	use Plugin_Integration;

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
		add_action( 'tribe_template_before_include:tickets-wallet-plus/pdf/pass/body/sidebar', [ $this, 'add_venue_to_pdf' ], 10, 3 );
	}

	/**
	 * Register filters.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_filters() {
		add_filter( 'tec_tickets_wallet_plus_pdf_pass_template_vars', [ $this, 'filter_pdf_template_context' ] );
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
		$this->container->make( Pdf::class )->add_tec_styles( $file, $name, $template );
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
	public function add_venue_to_pdf( $file, $name, $template ) {
		$this->container->make( Pdf::class )->add_venue( $file, $name, $template );
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
	public function filter_pdf_template_context( $ctx ): array {
		return $this->container->make( Pdf::class )->filter_template_context( $ctx );
	}
}
