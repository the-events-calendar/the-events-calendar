<?php
/**
 * Controls an Event query connecting it with the Repository and Context.
 *
 * @package Tribe\Events\Views\V2\Query
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Query;

use Tribe__Events__Main as TEC;

/**
 * Class Event_Query_Controller
 *
 * @package Tribe\Events\Views\V2\Query
 * @since 4.9.2
 */
class Event_Query_Controller extends Abstract_Query_Controller {

	/**
	 * {@inheritDoc}
	 */
	public function get_filter_name() {
		return 'events';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_post_types() {
		return [
			TEC::POSTTYPE,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function repository() {
		return tribe_events();
	}
}
