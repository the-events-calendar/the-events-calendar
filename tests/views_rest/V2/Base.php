<?php

namespace V2;

use Tribe\Events\Views\V2\View;
use Views_restTester as Tester;

class Base {

	protected $endpoint = 'v2';
	protected $home_url;

	public function _before( Tester $I ) {
		$this->home_url = $I->grabSiteUrl();
		// Let's make sure Views v2 are enabled.
		$I->setTribeOption( View::$option_enabled, true );
	}
}
