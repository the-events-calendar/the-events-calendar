<?php
/**
 * Handles (optionally) converting iCalendar export links to subscribe links.
 *
 * @since   4.6.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Events__iCal as iCal;

/**
 * Class Subscribe
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Subscribe {

	/**
	 * A placeholder for the option to keep
	 * "legacy" export links vs. the new subscription links.
	 *
	 * @since TBD
	 *
	 * @var boolean
	 */
	protected $toggle;

	public static $template = 'ical-link';

	public function register() {

		$this->hooks();
	}

	public function hooks() {
		add_filter( 'tribe_template_file', [ $this, 'replace_export_links' ], 10, 3 );
	}

	/**
	 * This method will replace the "The Events Calendar Extension: Events Control" metabox template
	 * with one that will not include the management of Online Events.
	 *
	 * @since 1.0.0
	 *
	 * @param string               $file The template file found for the template name.
	 * @param array<string>|string $name       The name, or name fragments, of the requested template.
	 * @param \Tribe__Template     $template   The template instance that is currently handling the template location
	 *                                                                                                     request.
	 *
	 * @return string The path to the template to load; this will be modified to the "doctored" metabox
	 *                template if required.
	 */
	public function replace_export_links( $file, $name, \Tribe__Template $template ) {
		if ( is_string( $name ) && static::$template !== $name ) {
			return $file;
		}

		if ( is_array( $name ) && ! in_array( static::$template, $name ) ) {
			return $file;
		}

		return $file;
	}
}
