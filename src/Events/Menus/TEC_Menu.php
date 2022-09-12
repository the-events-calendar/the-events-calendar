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

/**
 * Class Menu
 *
 * @since TBD
 *
 * @package TEC\Events\Menus
 */
class TEC_Menu extends Abstract_Menu {

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $page_title = 'The Events Calendar';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $menu_title = 'The Events Calendar';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $capability = 'edit_tribe_events';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $menu_slug = 'tec_events';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $icon_url = 'dashicons-calendar-alt';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public $position = '5';

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var boolean
	 */
	public $settings = true;

	/**
	 * Undocumented variable
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	public $settings_page_data = [];

	public function __construct() {
		$this->settings_file = plugin_dir_path( __FILE__ ) . 'Data.php';
		parent::__construct();
	}

	public function render() {
		?>
		<h1>Hi there!</h1>
		<?php
	}
}
