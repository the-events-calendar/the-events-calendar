<?php
/**
 * Formats a date to a precise value, one that will include microseconds.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */

namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Class Precise_Date_Formatter
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class Precise_Date_Formatter implements Formatter {
	/**
	 * A reference to a text formatter instance.
	 *
	 * @since TBD
	 *
	 * @var Text_Formatter
	 */
	private $formatter;

	/**
	 * Date_Formatter constructor.
	 *
	 * @since TBD
	 *
	 * @param Text_Formatter $formatter A reference to a text formatter instance.
	 */
	public function __construct( Text_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	/**
	 * {@inheritdoc }
	 */
	public function format( $value ) {
		if ( empty( $value ) ) {
			return null;
		}

		if ( $value instanceof \DateTimeInterface ) {
			return $value->format( 'Y-m-d H:i:s.u' );
		}

		return $this->formatter->format( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare() {
		return '%s';
	}
}
