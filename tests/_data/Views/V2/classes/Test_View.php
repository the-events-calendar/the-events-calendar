<?php

namespace Tribe\Events\Views\V2;


class Test_View extends View {

	protected $slug = 'test';

	public function get_html() {
		return __CLASS__;
	}

}