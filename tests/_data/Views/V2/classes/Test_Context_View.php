<?php
namespace Tribe\Events\Views\V2;

class Test_Context_View extends View {
	protected static $view_slug = 'test-context';

	public function get_html() {
		$html = '<script type="text/javascript">'
		        . 'const dump = ' . json_encode( $this->get_context()->to_array(), JSON_PRETTY_PRINT )
		        . '</script>';

		return $html;
	}
}
