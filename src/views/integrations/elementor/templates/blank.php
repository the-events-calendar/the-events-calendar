<?php
/**
 * Blank template file for implementing Event template i Elementor Free.
 *
 * @since TBD
 */
use TEC\Events\Integrations\Plugins\Elementor\Controller as Elementor_Integration;

if ( tribe( Elementor_Integration::class )->is_elementor_pro_active() ) {
	if ( ! elementor_theme_do_location( 'single' ) ) {
		the_content();
	}
} else {
	the_content();
}
