<?php
/**
 * Common helper methods for PRO widgets.
 */
class TribeEventsPro_Widgets {
	/**
	 * @param $filters
	 * @param $operand
	 * @return array|null
	 */
	public static function form_tax_query( $filters, $operand ) {
		if ( empty( $filters ) ) return null;

		$tax_query = array();

		foreach ( $filters as $tax => $terms ) {
			if ( empty( $terms ) ) continue;

			$tax_operand = 'AND';
			if ( $operand == 'OR' ) $tax_operand = 'IN';
			$arr = array( 'taxonomy' => $tax, 'field' => 'id', 'operator' => $tax_operand, 'terms' => $terms );
			$tax_query[] = $arr;
		}

		if ( count( $tax_query ) > 1 ) $tax_query['relation'] = $operand;
		return $tax_query;
	}
}