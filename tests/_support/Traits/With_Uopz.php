<?php

namespace Tribe\Events\Test\Traits;

trait With_Uopz {
	private $uopz_set_returns = [];

	public function uopz_set_return( $functionOrClass, $valueOrMethod, ...$rest_of_args ) {
		$target = is_callable( $functionOrClass ) ? $functionOrClass : [
			$functionOrClass,
			$valueOrMethod
		];
		$this->uopz_set_returns[] = $target;
		if ( is_array( $target ) && ! class_exists( $target[0] ) ) {
			eval( sprintf( 'class %s { public function %s(){}}', ...$target ) );
		}

		uopz_set_return( $functionOrClass, $valueOrMethod, ...$rest_of_args );
	}

	/**
	 * @after
	 */
	public function uopz_unset_returns() {
		foreach ( $this->uopz_set_returns as $fn ) {
			if ( is_array( $fn ) ) {
				uopz_unset_return( $fn[0], $fn[1] );
			} else {
				uopz_unset_return( $fn );
			}
		}
	}
}
