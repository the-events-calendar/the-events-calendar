<?php
/**
 * Stub for Elementor\TemplateLibrary\Source_Local when Elementor is not loaded in tests.
 *
 * @since TBD
 */

namespace Elementor\TemplateLibrary;

if ( ! class_exists( 'Elementor\TemplateLibrary\Source_Local' ) ) {
	class Source_Local {
		const CPT = 'elementor_library';
	}
}
