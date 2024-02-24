<?php

namespace Tribe\Events\Views\V2;


class Test_View extends View {
	protected static $view_slug = 'test';

	public function get_html() {
		return __CLASS__;
	}

	public function _public_repository_args() {
		return $this->get_repository_args();
	}

	public function _public_global_repository_args() {
		return $this->get_global_repository_args();
	}
}
