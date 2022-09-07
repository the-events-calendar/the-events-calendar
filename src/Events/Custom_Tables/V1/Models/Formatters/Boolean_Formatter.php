<?php
/**
 *
 *
 *
 * @since 6.0.0
 */

namespace TEC\Events\Custom_Tables\V1\Models\Formatters;


class Boolean_Formatter implements Formatter {

	public function format( $value ) {
		return $value ? 1 : 0;
	}

	public function prepare() {
		return '%d';
	}
}
