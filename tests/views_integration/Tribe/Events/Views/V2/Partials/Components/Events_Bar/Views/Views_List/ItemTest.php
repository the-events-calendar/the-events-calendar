<?php

namespace Tribe\Events\Views\V2\Partials\Components\Events_Bar\Views\Views_List;

use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class ItemTest extends HtmlPartialTestCase
{

	protected $partial_path = 'components/events-bar/views/list/item';

	/**
	 * Test render without view class name
	 */
	public function test_render_without_view_class_name() {
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}

	/**
	 * Test render with view class name not equal to current view
	 */
	public function test_render_with_view_class_name_not_equal_to_current_view() {
		$view_class_name = 'Tribe\Events\Views\V2\Views\Month_View';
		$view            = View::make( 'Tribe\Events\Views\V2\Views\List_View' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'view_class_name' => $view_class_name,
			'view'            => $view,
		] ) );
	}

	/**
	 * Test render with view class name equal to current view
	 */
	public function test_render_with_view_class_name_equal_to_current_view() {
		$view_class_name = 'Tribe\Events\Views\V2\Views\Month_View';
		$view            = View::make( 'Tribe\Events\Views\V2\Views\Month_View' );
		$this->assertMatchesSnapshot( $this->get_partial_html( [
			'view_class_name' => $view_class_name,
			'view'            => $view,
		] ) );
	}
}
