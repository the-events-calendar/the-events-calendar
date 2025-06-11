<?php
/**
 * Calendar Embeds Admin List Page.
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

use TEC\Common\Contracts\Container;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\Assets\Asset;
use TEC\Common\StellarWP\Assets\Assets;
use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use TEC\Events\Calendar_Embeds\Template;
use Tribe__Events__Main as TEC;

/**
 * Class List_Page
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class List_Page extends Controller_Contract {
	use Restore_Menu_Trait;

	/**
	 * The template.
	 *
	 * @since 6.11.0
	 *
	 * @var Template
	 */
	private Template $template;

	/**
	 * Page constructor.
	 *
	 * @since 6.11.0
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
	 * @since 6.11.0
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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_clipboard_script' ] );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'admin_menu', [ $this, 'register_menu_item' ], 11 );
		remove_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		remove_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ] );
		remove_action( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_custom_column', [ $this, 'manage_column_content' ] );
		remove_filter( 'manage_' . Calendar_Embeds::POSTTYPE . '_posts_columns', [ $this, 'manage_columns' ] );
		remove_action( 'admin_enqueue_scripts', [ $this, 'enqueue_clipboard_script' ] );
	}

	/**
	 * Customize columns for the table.
	 *
	 * @since 6.11.0
	 *
	 * @param array<string,string> $columns The columns.
	 *
	 * @return array<string,string> Filtered columns.
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
		 * @since 6.11.0
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
	 * @since 6.11.0
	 *
	 * @param string $column_name The name of the column.
	 * @param int    $post_id     The post ID.
	 *
	 * @return void
	 */
	public function manage_column_content( $column_name, $post_id ): void {
		if ( ! in_array( $column_name, [ 'event_categories', 'event_tags', 'snippet' ], true ) ) {
			return;
		}

		if ( Calendar_Embeds::POSTTYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$method = 'render_' . $column_name;
		$this->$method( $post_id );
	}

	/**
	 * Register the Calendar Embeds menu item.
	 *
	 * @since 6.11.0
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
	 * @since 6.11.0
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
	 * @since 6.11.0
	 *
	 * @return string
	 */
	public function get_page_title(): string {
		return __( 'Calendar Embeds', 'the-events-calendar' );
	}

	/**
	 * Keep parent menu open when adding and editing calendar embeds.
	 *
	 * @since 6.11.0
	 * @since 6.11.2.1 Made the parameters non-strict.
	 *
	 * @param ?string $submenu_file The current submenu file.
	 *
	 * @return ?string
	 */
	public function keep_parent_menu_open( $submenu_file ): ?string {
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
	 * @since 6.11.0
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
	 * Enqueue the clipboard script.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function enqueue_clipboard_script(): void {
		if ( ! self::is_on_page() && ! Singular_Page::is_on_page() ) {
			return;
		}

		wp_enqueue_script( 'thickbox' );
		Assets::init()->get( 'tec-copy-to-clipboard' )->enqueue();
	}

	/**
	 * Register assets for the Calendar Embeds page.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	protected function register_assets(): void {
		Asset::add(
			'tec-events-calendar-embeds-style',
			'css/calendar-embeds/admin/page.css'
		)
			->add_to_group_path( TEC::class )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( fn() => self::is_on_page() || Singular_Page::is_on_page() )
			->set_dependencies( 'thickbox', 'tribe-common-admin' )
			->register();
	}

	/**
	 * Render the event categories column.
	 *
	 * @since 6.11.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	protected function render_event_categories( int $post_id ): void {
		$categories = Calendar_Embeds::get_event_categories( $post_id );
		if ( empty( $categories ) ) {
			echo esc_html( __( 'All Categories', 'the-events-calendar' ) );
			return;
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
	}

	/**
	 * Render the event tags column.
	 *
	 * @since 6.11.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	protected function render_event_tags( int $post_id ): void {
		$tags = Calendar_Embeds::get_tags( $post_id );
		if ( empty( $tags ) ) {
			echo esc_html( __( 'All Tags', 'the-events-calendar' ) );
			return;
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
	}

	/**
	 * Render the embed snippet column.
	 *
	 * @since 6.11.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	protected function render_snippet( int $post_id ): void {
		$this->template->template(
			'embed-snippet-content',
			[
				'post_id' => $post_id,
			]
		);
	}
}
