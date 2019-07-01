<?php

namespace Tribe\Events\Views\V2;


class Test_Full_View extends View {

	protected $slug = 'test-full';

	public function get_html() {
		return $this->template->render();
	}
}
