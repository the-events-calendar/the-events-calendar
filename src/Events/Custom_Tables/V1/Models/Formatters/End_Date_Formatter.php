<?php
/**
 * Validates a date value.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */

namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

/**
 * Class Date_Formatter
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Formatters
 */
class End_Date_Formatter implements Formatter {
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
	public function __construct( Date_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	/**
	 * {@inheritdoc }
	 */
	public function format( $value ) {
		if ( empty( $value ) ) {
			return null;
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
