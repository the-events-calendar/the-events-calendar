<?php
namespace Helper;

use Codeception\TestInterface;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Views_integration extends \Codeception\Module {
	public function _before( TestInterface $test ) {
		$this->reset_post_type_caches();
	}

	public function _after( TestInterface $test ) {
		$this->reset_post_type_caches();
	}

	private function reset_post_type_caches() {
		// All views tests share a PHP request, but their fixtures can reuse post IDs.
		tribe_unset_var( 'Tribe__Events__Main::isOrganizer' );
		tribe_unset_var( 'Tribe__Events__Main::isVenue' );
	}
}
