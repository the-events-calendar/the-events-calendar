<?php

namespace V2;

use Views_restTester as Tester;

class Base {

	protected $endpoint = 'v2';
	protected $home_url;

	public function _before( Tester $I ) {
		$this->home_url = $I->grabSiteUrl();
	}
}
