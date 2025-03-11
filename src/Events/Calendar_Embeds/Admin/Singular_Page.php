<?php
/**
 * Calendar Embeds Admin Singular Page.
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
use WP_Post;

/**
 * Class Singular_Page
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class Singular_Page extends Controller_Contract {
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
		add_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ], 5 );
		add_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		add_action( 'edit_form_after_title', [ $this, 'calendar_embeds_editor' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ], 5 );
		remove_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		remove_action( 'edit_form_after_title', [ $this, 'calendar_embeds_editor' ] );
		remove_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Adds the metaboxes to the order post type.
	 *
	 * @since 5.13.3
	 *
	 * @param string  $post_type The post type.
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function add_meta_boxes( $post_type, $post ): void {
		if ( Calendar_Embeds::POSTTYPE !== $post_type ) {
			return;
		}

		add_meta_box(
			'tec-events-calendar-embeds-preview',
			__( 'Embed Preview', 'the-events-calendar' ),
			[ $this, 'render_embed_preview' ],
			$post_type,
			'normal',
			'high'
		);

		// phpcs:disable Squiz.Commenting.InlineComment.SpacingBeforeExpected, Universal.WhiteSpace.DisallowInlineTabs.NonIndentTabsUsedSpaces
		global $wp_meta_boxes;

		$meta_box = $wp_meta_boxes[ get_current_screen()->id ]['side']['core']['submitdiv'] ?? false;

		// Remove core's Publish metabox and add our own.
		// remove_meta_box( 'submitdiv', $post_type, 'side' );
		// add_meta_box(
		// 	'submitdiv',
		// 	__( 'Actions', 'event-tickets' ),
		// 	[ $this, 'render_actions' ],
		// 	$post_type,
		// 	'side',
		// 	'high',
		// 	$meta_box['args'] ?? []
		// );

		// phpcs:enable Squiz.Commenting.InlineComment.SpacingBeforeExpected, Universal.WhiteSpace.DisallowInlineTabs.NonIndentTabsUsedSpaces
	}

	/**
	 * Renders the preview of the embed metabox.
	 *
	 * @since TBD
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_embed_preview( WP_Post $post ): void {
		// phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscapedExpected, WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Calendar_Embeds::get_iframe( $post->ID );
	}

	/**
	 * Renders the Calendar Embeds editor.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function calendar_embeds_editor(): void {
		if ( ! $this->is_on_page() ) {
			return;
		}

		/**
		 * Fires before the Calendar Embeds editor.
		 *
		 * @since TBD
		 */
		do_action( 'tec_calendar_embeds_before_editor' );

		/**
		 * Fires after the Calendar Embeds editor.
		 *
		 * @since TBD
		 */
		do_action( 'tec_calendar_embeds_after_editor' );
	}

	/**
	 * Gets the URL for a Calendar Embed.
	 *
	 * @since TBD
	 *
	 * @param int $id The embed id.
	 *
	 * @return string
	 */
	public function get_url( int $id ): string {
		return add_query_arg(
			[
				'post'   => $id,
				'action' => 'edit',
			],
			admin_url( 'post.php' )
		);
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
			'parent_file'  => $parent_file,
			'submenu_file' => $submenu_file,
		];

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$parent_file = 'edit.php?post_type=' . TEC::POSTTYPE;

		return 'edit.php?post_type=' . Calendar_Embeds::POSTTYPE;
	}

	/**
	 * Check if the current screen is the Calendar Embeds page.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	protected function is_on_page(): bool {
		global $pagenow, $post_type;

		return Calendar_Embeds::POSTTYPE === $post_type && ( 'post-new.php' === $pagenow || 'post.php' === $pagenow );
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
			'tec-events-calendar-embeds-single-script',
			'editor.js'
		)
			->add_to_group_path( 'tec-events-calendar-embeds' )
			->enqueue_on( 'tec_calendar_embeds_before_editor' )
			->set_dependencies(
				'wp-hooks',
			)
			->in_footer()
			->register();

		Asset::add(
			'tec-events-calendar-embeds-single-style',
			'editor.css'
		)
			->add_to_group_path( 'tec-events-calendar-embeds' )
			->enqueue_on( 'tec_calendar_embeds_before_editor' )
			->set_dependencies( 'tribe-common-admin' )
			->register();
	}
}
