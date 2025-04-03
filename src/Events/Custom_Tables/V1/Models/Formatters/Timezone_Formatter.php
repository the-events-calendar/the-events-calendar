<?php
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

class Timezone_Formatter implements Formatter {
	/**
	 * @var Text_Formatter
	 */
	private $formatter;

	public function __construct( Text_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	public function format( $value ) {
		if ( $value instanceof \DateTimeZone ) {
			return sanitize_text_field( $value->getName() );
		}

		return $this->formatter->format( $value );
	}

	public function prepare() {
		return '%s';
	}
}
