<?php

namespace TEC\Events\Configuration;

use TEC\Common\Configuration\Configuration_Loader;
use TEC\Common\Configuration\Constants_Provider;
use TEC\Common\Provider\Controller;

class Provider extends Controller {

	protected function do_register(): void {
		tribe( Configuration_Loader::class )->add( new Constants_Provider() );
		// @todo
	}

	public function unregister(): void {
		// @todo
	}
}