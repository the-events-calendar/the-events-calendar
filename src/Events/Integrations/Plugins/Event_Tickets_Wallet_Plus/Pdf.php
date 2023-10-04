<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets_Wallet_Plus;

/**
 * Class Pdf
 * 
 * @since TBD
 * 
 * @package TEC\Events\Integrations\Plugins\Event_Tickets_Wallet_Plus
 */
class Pdf {

	/**
	 * Filter PDF template vars.
	 *
	 * @since TBD
	 * 
	 * @param array $vars
	 * 
	 * @return array
	 */
	public function filter_pdf_template_vars( $vars ): array {
		return $vars;
	}

	/**
	 * Add styles to PDF.
	 * 
	 * @since TBD
	 * 
	 * @param string           $file     Path to the file.
	 * @param string           $name     Name of the file.
	 * @param \Tribe__Template $template Template instance.
	 * 
	 * @return void
	 */
	public function add_tec_styles_to_pdf( $file, $name, $template ): void {
		if ( ! $template instanceof \Tribe__Template ) {
			return;
		}

		tribe( Template::class )->template( 'pdf/pass/tec-styles', $template->get_local_values(), true );
	}

	/**
	 * Maybe add venue to PDF.
	 * 
	 * @since TBD
	 * 
	 * @param string           $file     Path to the file.
	 * @param string           $name     Name of the file.
	 * @param \Tribe__Template $template Template instance.
	 * 
	 * @return void
	 */
	public function maybe_add_venue_to_pdf( $file, $name, $template ): void {
		if ( ! $template instanceof \Tribe__Template ) {
			return;
		}

		tribe( Template::class )->template( 'pdf/pass/body/venue', $template->get_local_values(), true );
	}
}