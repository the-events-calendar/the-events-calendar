<?php
/**
 * Validates a date value.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */

namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

use Tribe__Date_Utils as Dates;

/**
 * Class Date_Formatter
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class Date_Formatter implements Formatter {
	/**
	 * A reference to a text formatter instance.
	 *
	 * @since 6.0.0
	 *
	 * @var Text_Formatter
	 */
	private $formatter;

	/**
	 * Date_Formatter constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param  Text_Formatter  $formatter  A reference to a text formatter instance.
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
			return $value->format( Dates::DBDATETIMEFORMAT );
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
