<?php

namespace Tribe\Events\Views\V2;


class Publicly_Visible_Test_View extends View {

	protected $slug = 'publicly-visible-test';

	protected $publicly_visible = true;

	public function get_html() {
		return __CLASS__;
	}

}