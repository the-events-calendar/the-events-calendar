<?php
/**
 * Formats the RSET value of an Event model.
 *
 * The free plugin treats the RSET as an opaque, multi-line string: dates-only RSETs are
 * the ones it authors and expands, rule-based RSETs are authored and expanded by Events
 * Calendar Pro and preserved verbatim here.
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */

namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Class Rset_Formatter
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class Rset_Formatter implements Formatter {
	/**
	 * {@inheritdoc}
	 */
	public function format( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
			return (string) $value;
		}

		return is_string( $value ) ? $value : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare() {
		return '%s';
	}
}
