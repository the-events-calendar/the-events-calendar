<?php
/**
 * Handles the manipulation of the promo to correctly render it in v2 views.
 *
 * @since   5.1.5
 *
 * @package Tribe\Events\Views\V2\Template
 */

namespace Tribe\Events\Views\V2\Template;

/**
 * Class Promo
 *
 * @since   5.1.5
 *
 * @package Tribe\Events\Views\V2\Template
 */
class Promo {

	/**
	 * Include the promo banner after the after component.
	 *
	 * @since 5.1.5
	 *
	 * @param string   $file     Complete path to include the PHP File.
	 * @param array    $name     Template name.
	 * @param Template $template Current instance of the Template.
	 *
	 * @return void  Template render has no return.
	 */
	public function action_add_promo_banner( $file, $name, $template ) {
		if ( ! tribe_get_option( 'donate-link', false ) ) {
			return;
		}

		tribe_events_promo_banner();
	}
}
