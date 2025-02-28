<?php
/**
 * Manages the External Calendar Embeds Feature.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since TBD

 * @package TEC\Events\Calendar_Embeds
 */
class Controller extends Controller_Contract {

	/**
	 * @inheritDoc
	 */
	public function do_register(): void {
		$this->container->singleton( Calendar_Embeds::class );
		$this->container->singleton( Admin\List_Table::class );
		$this->container->singleton( Admin\Page::class );

		add_action(
			'init',
			[
				$this->container->make( Calendar_Embeds::class ),
				'register_post_type',
			]
		);
		add_action(
			'admin_menu',
			[
				$this->container->make( Admin\Page::class ),
				'register_menu_item',
			],
			11
		);
		add_action(
			'admin_init',
			[
				$this->container->make( Admin\Page::class ),
				'register_assets',
			]
		);
		add_action(
			'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column',
			[
				$this->container->make( Admin\List_Table::class ),
				'manage_column_content',
			],
			10,
			2
		);
		add_filter(
			'submenu_file',
			[
				$this->container->make( Admin\Page::class ),
				'keep_parent_menu_open',
			]
		);
		add_filter(
			'manage_' . Calendar_Embeds::POSTTYPE . '_posts_columns',
			[
				$this->container->make( Admin\List_Table::class ),
				'manage_columns',
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function unregister(): void {
		remove_action(
			'init',
			[
				$this->container->make( Calendar_Embeds::class ),
				'register_post_type',
			]
		);
		remove_action(
			'admin_menu',
			[
				$this->container->make( Admin\Page::class ),
				'register_menu_item',
			],
			11
		);
		remove_action(
			'admin_init',
			[
				$this->container->make( Admin\Page::class ),
				'register_assets',
			]
		);
		remove_action(
			'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column',
			[
				$this->container->make( Admin\List_Table::class ),
				'manage_column_content',
			],
			10
		);
		remove_filter(
			'submenu_file',
			[
				$this->container->make( Admin\Page::class ),
				'keep_parent_menu_open',
			]
		);
		remove_filter(
			'manage_' . Calendar_Embeds::POSTTYPE . '_posts_columns',
			[
				$this->container->make( Admin\List_Table::class ),
				'manage_columns',
			]
		);
	}
}
