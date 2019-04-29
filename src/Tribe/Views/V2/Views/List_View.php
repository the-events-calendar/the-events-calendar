<?php
/**
 * The List View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views;


use Tribe\Events\Views\V2\View;

class List_View extends View {

	public function get_html() {
		return <<< HTML
<p>Hi there from List View!</p>
HTML;
	}

}