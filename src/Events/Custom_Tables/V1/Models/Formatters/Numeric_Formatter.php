<?php
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

class Numeric_Formatter implements Formatter {
	public function format( $value ) {
		return (int) $value;
	}

	public function prepare() {
		return '%d';
	}
}
