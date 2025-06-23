<?php
/**
 * Calendar Embeds Admin Singular Page.
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */

namespace TEC\Events\Calendar_Embeds\Admin;

use TEC\Common\Contracts\Container;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use TEC\Events\Calendar_Embeds\Template;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Singular_Page
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds\Admin
 */
class Singular_Page extends Controller_Contract {
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
		add_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ], 5 );
		add_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10, 2 );
		add_filter( 'tec_events_calendar_embeds_iframe', [ $this, 'replace_iframe_markup' ], 10, 2 );
		add_action( 'post_submitbox_minor_actions', [ $this, 'add_copy_embed_button' ] );
		add_filter( 'post_updated_messages', [ $this, 'modify_post_updated_messages' ] );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ], 5 );
		remove_action( 'adminmenu', [ $this, 'restore_menu_globals' ] );
		remove_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		remove_filter( 'tec_events_calendar_embeds_iframe', [ $this, 'replace_iframe_markup' ] );
		remove_action( 'post_submitbox_minor_actions', [ $this, 'add_copy_embed_button' ] );
		remove_filter( 'post_updated_messages', [ $this, 'modify_post_updated_messages' ] );
	}

	/**
	 * Modifies the post updated messages for the calendar embed post type.
	 *
	 * @since 6.11.0
	 * @since 6.11.2.1 Made the parameters non-strict.
	 *
	 * @param array $messages The post updated messages.
	 *
	 * @return array
	 */
	public function modify_post_updated_messages( $messages ): array {
		$messages = (array) $messages;

		if ( ! self::is_on_page() ) {
			return $messages;
		}

		global $post;

		$permalink = get_permalink( $post->ID );
		if ( ! $permalink ) {
			$permalink = '';
		}

		$view_post_link_html = sprintf(
			' <a href="%1$s">%2$s</a>',
			esc_url( $permalink ),
			__( 'View Calendar Embed', 'the-events-calendar' )
		);

		$scheduled_date = sprintf(
			/* translators: Publish box date string. 1: Date, 2: Time. */
			__( '%1$s at %2$s', 'the-events-calendar' ),
			/* translators: Publish box date format, see https://www.php.net/manual/datetime.format.php */
			date_i18n( _x( 'M j, Y', 'publish box date format', 'the-events-calendar' ), strtotime( $post->post_date ) ),
			/* translators: Publish box time format, see https://www.php.net/manual/datetime.format.php */
			date_i18n( _x( 'H:i', 'publish box time format', 'the-events-calendar' ), strtotime( $post->post_date ) )
		);

		$messages[ Calendar_Embeds::POSTTYPE ] = [
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Calendar Embed updated.', 'the-events-calendar' ) . $view_post_link_html,
			2  => __( 'Custom field updated.', 'the-events-calendar' ),
			3  => __( 'Custom field deleted.', 'the-events-calendar' ),
			4  => __( 'Calendar Embed updated.', 'the-events-calendar' ),
			/* translators: %s: Date and time of the revision. */
			5  => tec_get_request_var_raw( 'revision', 0 ) ? sprintf( __( 'Calendar Embed restored to revision from %s.', 'the-events-calendar' ), wp_post_revision_title( (int) tec_get_request_var_raw( 'revision', 0 ), false ) ) : false,
			6  => __( 'Calendar Embed published.', 'the-events-calendar' ) . $view_post_link_html,
			7  => __( 'Calendar Embed saved.', 'the-events-calendar' ),
			8  => __( 'Calendar Embed submitted.', 'the-events-calendar' ),
			/* translators: %s: Scheduled date for the Calendar Embed. */
			9  => sprintf( __( 'Calendar Embed scheduled for: %s.', 'the-events-calendar' ), '<strong>' . $scheduled_date . '</strong>' ),
			10 => __( 'Calendar Embed draft updated.', 'the-events-calendar' ),
		];

		return $messages;
	}

	/**
	 * Adds the copy embed button to the post submitbox.
	 *
	 * @since 6.11.0
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function add_copy_embed_button( WP_Post $post ): void {
		if ( ! self::is_on_page() ) {
			return;
		}

		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$this->template->template(
			'copy-embed-button-in-metabox',
			[
				'post_id' => $post->ID,
			]
		);
	}

	/**
	 * Replaces the iframe markup with a placeholder if the embed is not saved.
	 *
	 * @since 6.11.0
	 *
	 * @param string  $iframe The iframe markup.
	 * @param WP_Post $embed  The embed post object.
	 *
	 * @return string
	 */
	public function replace_iframe_markup( string $iframe, WP_Post $embed ): string {
		if ( ! self::is_on_page() ) {
			return $iframe;
		}

		if ( 'auto-draft' !== $embed->post_status ) {
			return $iframe;
		}

		return '<p><strong>' . esc_html__( 'Please save the embed to see the preview.', 'the-events-calendar' ) . '</strong></p>';
	}

	/**
	 * Adds the metaboxes to the order post type.
	 *
	 * @since 6.11.0
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

		// Removes not editable slug metabox to avoid confusion.
		remove_meta_box( 'slugdiv', $post_type, 'normal' );
	}

	/**
	 * Renders the preview of the embed metabox.
	 *
	 * @since 6.11.0
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_embed_preview( WP_Post $post ): void {
		// phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscaped, WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Calendar_Embeds::get_iframe( $post->ID );
	}

	/**
	 * Gets the URL for a Calendar Embed.
	 *
	 * @since 6.11.0
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
	 * @since 6.11.0
	 *
	 * @return bool
	 */
	public static function is_on_page(): bool {
		global $pagenow, $post_type;

		return Calendar_Embeds::POSTTYPE === $post_type && ( 'post-new.php' === $pagenow || 'post.php' === $pagenow );
	}
}
