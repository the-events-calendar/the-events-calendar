<?php
/**
 * The main service provider for the version 2 of the Widgets.
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

use Tribe\Events\Views\V2\Views\Widgets\List_Widget_View;
use Tribe\Events\Views\V2\Views\Widgets\Widget_List_View;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Widgets
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Variable that holds the name of the widgets being created
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	private $widgets = [
		'tribe_events_list_widget',
	];

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$this->hook();
	}

	/**
	 * Function used to attach the hooks associated with this class.
	 *
	 * @since TBD
	 */
	public function hook() {
		add_filter( 'tribe_widgets', [ $this, 'register_widget' ] );
		add_filter( 'tribe_events_views', [ $this, 'add_views' ] );
	}

	/**
	 * Add the widgets to register with WordPress.
	 *
	 * @since TBD
	 *
	 * @param array $widgets An array of widget classes to register.
	 *
	 * @return mixed
	 */
	public function register_widget( $widgets ) {
		$widgets['tribe_events_list_widget'] = Widget_List::class;

		return $widgets;
	}

	/**
	 * @todo
	 *
	 * @since TBD
	 *
	 * @param $views
	 *
	 * @return mixed
	 */
	public function add_views( $views ) {
		$views['widget-list'] = Widget_List_View::class;

		return $views;
	}
}
