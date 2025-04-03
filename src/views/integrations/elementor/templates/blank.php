<?php
/**
 * Blank template file for implementing Event template i Elementor Free.
 *
 * @since 6.4.0
 */
use TEC\Events\Integrations\Plugins\Elementor\Controller as Elementor_Integration;

/**
 * elementor_theme_do_location() will print a Theme Builder Location for a "single" in this case.
 */
if ( tribe( Elementor_Integration::class )->is_elementor_pro_active() && elementor_theme_do_location( 'single' ) ) {
	return;
}

the_content();
