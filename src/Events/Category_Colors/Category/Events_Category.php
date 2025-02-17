<?php
/**
 * Class for handling event category colors.
 *
 * @since   TBD
 * @package TEC\Events\Category_Colors\Category
 */

namespace TEC\Events\Category_Colors\Category;

use Tribe__Events__Main;

/**
 * Class representing event categories with color settings.
 * This class extends `Category_Abstract` to handle event-specific category colors
 * within the `tribe_events_cat` taxonomy.
 *
 * @since TBD
 */
class Events_Category extends Category_Abstract {
	/**
	 * Taxonomy name associated with event categories.
	 *
	 * @since TBD
	 * @var string
	 */
	protected static $taxonomy = Tribe__Events__Main::TAXONOMY;
}
