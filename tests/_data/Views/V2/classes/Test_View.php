<?php

namespace Tribe\Events\Views\V2;


class Test_View extends View {

	public function get_slug() {
		return 'test';
	}

	public function get_html() {
		return __CLASS__;
	}

}