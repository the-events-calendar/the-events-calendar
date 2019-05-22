<?php
/**
 * Initializer for The Events Calendar for the template structure using Event
 *
 * Can be changed on Events > Settings > Display
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
namespace Tribe\Events\Views\V2\Template;

use Tribe__Events__Main as TEC;
use Tribe\Events\Views\V2\Index as Index;

class Event {
	/**
	 * Determines the Path for the PHP file to be used as the main template
	 * For Page base template setting it will select from theme or child theme
	 *
	 * @todo  Integrate with Template + Context classes
	 *
	 * @since  4.9.2
	 *
	 * @return string
	 */
	public function get_path() {
		$path = ( new Index() )->get_template_file( 'index' );
		return $path;
	}
}
