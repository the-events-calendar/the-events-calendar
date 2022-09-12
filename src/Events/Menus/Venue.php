<?php
/**
 * The base, abstract, class modeling a menu.
 *
 * This class does nothing by itself - it is meant to be extended for specific menus,
 * changing the properties as appropriate.
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */

namespace TEC\Events\Menus;

use TEC\Common\Menus\Abstract_Menu;
use TEC\Common\Menus\Traits\Submenu;
use TEC\Events\Menus\TEC_Menu;
use Tribe__Events__Venue;

/**
 * Class Venue
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class Venue extends Abstract_Menu {
	use Submenu;

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $page_title = 'The Events Calendar';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $menu_title = 'The Events Calendar Venues';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $capability = 'edit_tribe_venues';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $icon_url = 'dashicons-calendar-alt';

	protected $callback = '';

	public static $menu_slug = 'post-new.php?post_type=' . Tribe__Events__Venue::POSTTYPE;

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $position = '5';

	public function __construct() {
		$this->parent_slug = tribe( TEC_Menu::class )->get_slug();

		$this->page_title = sprintf(
			esc_html__( 'Add New %s',
			'the-events-calendar' ),
			'Venue'
		);

		$this->menu_title = sprintf(
			esc_html__( 'Add New %s',
			'the-events-calendar' ),
			'Venue'
		);

		parent::__construct();
	}

	public function render() {
		?>
		<h1>Hi there!</h1>
		<?php
	}
}
