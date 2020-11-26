<?php
/**
 * Handles Views v2 Customizer settings.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Customizer
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Customizer {
	/**
	 * Filters the currently registered Customizer sections to add or modify them.
	 *
	 * @since TBD
	 *
	 * @param array<string,array<string,array<string,int|float|string>>> $sections   The registered Customizer sections.
	 * @param \Tribe___Customizer                                        $customizer The Customizer object.
	 *
	 * @return array<string,array<string,array<string,int|float|string>>> The filtered sections.
	 */
	public function filter_sections( array $sections, $customizer ) {
		// TODO Filter the sections.
		return $sections;
	}
}
