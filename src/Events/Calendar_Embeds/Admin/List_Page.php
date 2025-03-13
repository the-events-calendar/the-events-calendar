<?php
/**
 * Calendar Embeds Admin List Page.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

use TEC\Common\Contracts\Container;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use TEC\Events\Calendar_Embeds\Template;
use Tribe__Events__Main as TEC;

/**
 * Class List_Page
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class List_Page extends Controller_Contract {
	use Restore_Menu_Trait;

	/**
	 * The template.
	 *
	 * @since TBD
	 *
	 * @var Template
	 */
	private Template $template;

	/**
	 * Page constructor.
	 *
	 * @since TBD
	 *
	 * @param Container $container  The container.
	 * @param Template  $template   The template.
	 */
	public function __construct( Container $container, Template $template ) {
		parent::__construct( $container );

		$this->template = $template;
	}

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->register_assets();
		add_action( 'admin_menu', [ $this, 'register_menu_item' ], 11 );
		add_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		add_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ] );
		add_action( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column', [ $this, 'manage_column_content' ], 10, 2 );
		add_filter( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_columns', [ $this, 'manage_columns' ] );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'admin_menu', [ $this, 'register_menu_item' ], 11 );
		remove_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		remove_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ] );
		remove_action( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column', [ $this, 'manage_column_content' ] );
		remove_filter( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_columns', [ $this, 'manage_columns' ] );
	}

	/**
	 * Customize columns for the table.
	 *
	 * @since TBD
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 */
	public function manage_columns( array $columns ): array {
		$new_columns = [
			'cb'               => $columns['cb'] ?? '<input type="checkbox" />',
			'title'            => __( 'Calendar Embeds', 'the-events-calendar' ),
			'event_categories' => __( 'Categories', 'the-events-calendar' ),
			'event_tags'       => __( 'Tags', 'the-events-calendar' ),
			'snippet'          => __( 'Embed Snippet', 'the-events-calendar' ),
		];

		/**
		 * Filters the columns for the calendar embeds list table.
		 *
		 * @since TBD
		 *
		 * @param array $new_columns The columns.
		 *
		 * @return array The filtered columns.
		 */
		return (array) apply_filters( 'tec_events_calendar_embeds_list_table_columns', $new_columns );
	}

	/**
	 * Customize the content of the columns.
	 *
	 * @since TBD
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_id     The post ID.
	 *
	 * @return void
	 */
	public function manage_column_content( $column_name, $post_id ): void {
		switch ( $column_name ) {
			case 'event_categories':
				// Get events categores from post meta.
				$categories = Calendar_Embeds::get_event_categories( $post_id );
				if ( empty( $categories ) ) {
					echo esc_html( __( 'All Categories', 'the-events-calendar' ) );
					break;
				}

				$cat_markup = [];
				foreach ( $categories as $category ) {
					ob_start();
					?>
					<a href="<?php echo esc_url( get_edit_term_link( $category, TEC::TAXONOMY ) ); ?>">
						<?php echo esc_html( trim( $category->name ) ); ?></a>
					<?php
					$cat_markup[] = ob_get_clean();
				}

				// phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscapedExpected, WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped
				echo implode( ', ', array_map( 'trim', $cat_markup ) );
				break;
			case 'event_tags':
				// Get events tags from post meta.
				$tags = Calendar_Embeds::get_tags( $post_id );
				if ( empty( $tags ) ) {
					echo esc_html( __( 'All Tags', 'the-events-calendar' ) );
					break;
				}

				$tag_markup = [];
				foreach ( $tags as $tag ) {
					ob_start();
					?>
					<a href="<?php echo esc_url( get_edit_term_link( $tag, 'post_tag' ) ); ?>">
						<?php echo esc_html( trim( $tag->name ) ); ?></a>
					<?php
					$tag_markup[] = ob_get_clean();
				}

				// phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscapedExpected, WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped
				echo implode( ', ', array_map( 'trim', $tag_markup ) );
				break;
			case 'snippet':
				$this->template->template(
					'embed-snippet-content',
					[
						'post_id' => $post_id,
					]
				);

				break;
		}
	}

	/**
	 * Register the Calendar Embeds menu item.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_menu_item(): void {
		/** @var \Tribe\Admin\Pages */
		$admin_pages = tribe( 'admin.pages' );

		$admin_pages->register_page(
			[
				'id'         => 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE,
				'path'       => 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE,
				'parent'     => 'edit.php?post_type=' . TEC::POSTTYPE,
				'title'      => $this->get_page_title(),
				'position'   => 8.4,
				'callback'   => null,
				'capability' => 'edit_published_tribe_events',
			]
		);
	}

	/**
	 * Gets the URL for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @param array $args The query args.
	 *
	 * @return string
	 */
	public function get_url( array $args = [] ): string {
		return add_query_arg(
			array_merge(
				[
					'post_type' => Calendar_Embeds::POSTTYPE,
				],
				$args
			),
			admin_url( 'edit.php' )
		);
	}

	/**
	 * Gets the Page title for the Calendar Embeds.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_page_title(): string {
		return __( 'Embed Calendar', 'the-events-calendar' );
	}

	/**
	 * Keep parent menu open when adding and editing calendar embeds.
	 *
	 * @since TBD
	 *
	 * @param string $submenu_file The current submenu file.
	 *
	 * @return ?string
	 */
	public function keep_parent_menu_open( ?string $submenu_file ): ?string {
		global $parent_file;

		if ( 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE !== $parent_file ) {
			return $submenu_file;
		}

		self::$stored_globals = [
			'parent_file' => $parent_file,
		];

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$parent_file = 'edit.php?post_type=' . TEC::POSTTYPE;

		return $submenu_file;
	}

	/**
	 * Check if the current screen is the Calendar Embeds page.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public static function is_on_page(): bool {
		/** @var \Tribe\Admin\Pages */
		$admin_pages = tribe( 'admin.pages' );
		$admin_page  = $admin_pages->get_current_page();

		return ! empty( $admin_page ) && 'edit-' . Calendar_Embeds::POSTTYPE === $admin_page;
	}

	/**
	 * Register assets for the Calendar Embeds page.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function register_assets(): void {
		Asset::add(
			'tec-events-calendar-embeds-script',
			'js/calendar-embeds/admin/page.js'
		)
			->add_to_group_path( 'tec-events-resources' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( fn() => self::is_on_page() || Singular_Page::is_on_page() )
			->set_dependencies( 'thickbox', 'tribe-clipboard' )
			->in_footer()
			->register();

		Asset::add(
			'tec-events-calendar-embeds-style',
			'css/calendar-embeds/admin/page.css'
		)
			->add_to_group_path( 'tec-events-resources' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( fn() => self::is_on_page() || Singular_Page::is_on_page() )
			->set_dependencies( 'thickbox', 'tribe-common-admin' )
			->register();
	}
}
