<?php

namespace Tribe\Events\Test\Testcases;

use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;
use Tribe\Test\Products\Traits\With_Uopz;

class TecViewTestCase extends ViewTestCase {

	use With_Uopz;

	public function setUp() {
		$this->set_fn_return(
			'date',
			static fn ( $format, $ts = null ) => date( $format, $ts ?? time() ),
			true
		);
		$this->set_fn_return(
			'time',
			static fn() => time(),
			true
		);
		parent::setUp();
	}
}
