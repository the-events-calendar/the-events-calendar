<?php

namespace Tribe\Events\Views\V2;


class Test_Template_View extends View {
	protected static $view_slug = 'test-template';

	public function get_html() {
		return __CLASS__;
	}

}
