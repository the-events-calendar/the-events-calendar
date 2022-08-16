<?php
namespace TEC\Events\Custom_Tables\V1\Models\Formatters;


class Text_Formatter implements Formatter {
	public function format( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return trim( sanitize_text_field( $value ) );
	}

	public function prepare() {
		return '%s';
	}
}
