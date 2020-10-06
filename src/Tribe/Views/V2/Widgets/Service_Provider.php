<?php
/**
 * The main service provider for the version 2 of the Widgets.
 *
 * @since   TBD
 * @package Tribe\Events\Views\V2\Widgets
 */

namespace Tribe\Events\Views\V2\Widgets;

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
		add_filter( 'tribe_widgets', [ $this, 'add_widgets' ] );
	}

	/**
	 *
	 *
	 * @since TBD
	 *
	 * @param $widgets
	 *
	 * @return mixed
	 */
	public function add_widgets( $widgets ) {
		$widgets['tribe_events_list_widget'] = List_Widget::class;

		return $widgets;
	}
}
