<?php
/**
 * External Calendar Embeds Controller.
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use WP_Post;
use Tribe__Events__Main as TEC_Plugin;
use WP_Term;
use TEC\Common\StellarWP\DB\DB;
use WP_Screen;

/**
 * Class Calendar_Embeds
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds
 */
class Calendar_Embeds extends Controller_Contract {

	/**
	 * Calendar Embeds post type slug.
	 *
	 * @since 6.11.0
	 *
	 * @var string
	 */
	const POSTTYPE = 'tec_calendar_embed';

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function do_register(): void {
		add_action( 'init', [ $this, 'register_post_type' ], 15 );
		add_action( 'tribe_events_views_v2_before_make_view_for_rest', [ Render::class, 'maybe_toggle_hooks_for_rest' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $this, 'disable_slug_changes' ], 10, 4 );
		add_filter( 'get_terms', [ $this, 'modify_term_count_on_term_list_table' ], 10, 2 );
		add_action( 'template_redirect', [ $this, 'redirect_to_embed' ] );
		add_filter( 'add_trashed_suffix_to_trashed_posts', [ $this, 'do_not_add_trashed_suffix_to_trashed_calendar_embeds' ], 10, 3 );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'init', [ $this, 'register_post_type' ], 15 );
		remove_action( 'tribe_events_views_v2_before_make_view_for_rest', [ Render::class, 'maybe_toggle_hooks_for_rest' ] );
		remove_filter( 'wp_insert_post_data', [ $this, 'disable_slug_changes' ] );
		remove_filter( 'get_terms', [ $this, 'modify_term_count_on_term_list_table' ] );
		remove_action( 'template_redirect', [ $this, 'redirect_to_embed' ] );
		remove_filter( 'add_trashed_suffix_to_trashed_posts', [ $this, 'do_not_add_trashed_suffix_to_trashed_calendar_embeds' ] );
	}

	/**
	 * Redirects to the embed URL when viewing a calendar embed post.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function redirect_to_embed(): void {
		if ( ! is_singular( static::POSTTYPE ) ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if ( is_embed() ) {
			return;
		}

		$url = get_post_embed_url( get_queried_object_id() );
		wp_safe_redirect( $url, 302, 'Calendar Embed Redirect' ); // phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit, StellarWP.CodeAnalysis.RedirectAndDie.Error
		tribe_exit();
	}

	/**
	 * Modifies the term count on the term list tables to ignore Calendar embeds from their count.
	 *
	 * @since 6.11.0
	 * @since 6.11.0.1 Added check to ensure ABSPATH/wp-admin/includes/screen.php is loaded before running.
	 * @since 6.11.2.1 Made the parameters non-strict.
	 *
	 * @param array  $terms      The terms.
	 * @param ?array $taxonomies The taxonomies.
	 *
	 * @return array
	 */
	public function modify_term_count_on_term_list_table( $terms, $taxonomies = null ): array {
		if ( null === $taxonomies ) {
			return $terms;
		}

		$terms      = (array) $terms;
		$taxonomies = (array) $taxonomies;

		if ( ! in_array( TEC_Plugin::TAXONOMY, $taxonomies, true ) && ! in_array( 'post_tag', $taxonomies, true ) ) {
			return $terms;
		}

		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			// Should only run on BE and after ABSPATH/wp-admin/includes/screen.php is loaded.
			return $terms;
		}

		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return $terms;
		}

		if ( $screen->id !== 'edit-' . TEC_Plugin::TAXONOMY && $screen->id !== 'edit-post_tag' ) {
			return $terms;
		}

		foreach ( $terms as &$term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$term->count -= (int) DB::get_var(
				DB::prepare(
					'SELECT COUNT( t.object_ID ) FROM %i t INNER JOIN %i p ON t.object_id = p.ID INNER JOIN %i tt ON tt.term_taxonomy_id = t.term_taxonomy_id WHERE tt.term_id = %d AND p.post_type = %s',
					DB::prefix( 'term_relationships' ),
					DB::prefix( 'posts' ),
					DB::prefix( 'term_taxonomy' ),
					$term->term_id,
					static::POSTTYPE
				)
			);

			$term->count = max( 0, $term->count );
		}

		return $terms;
	}

	/**
	 * Disables slug changes for the calendar embed post type.
	 *
	 * @since 6.11.0
	 * @since 6.11.2.1 Made the parameters non-strict.
	 *
	 * @param array $data              The post data.
	 * @param array $post_array        The post array.
	 * @param array $unsafe_post_array The unsanitized post array.
	 * @param bool  $update            Whether the post is being updated.
	 *
	 * @return array
	 */
	public function disable_slug_changes( $data, $post_array, $unsafe_post_array, $update ): array {
		if ( static::POSTTYPE !== $data['post_type'] ) {
			return $data;
		}

		$update     = (bool) $update;
		$data       = (array) $data;
		$post_array = (array) $post_array;

		if ( $update ) {
			// Ensure the post name is not updated.
			$data['post_name'] = str_replace( '__trashed', '', get_post( $post_array['ID'] )->post_name );

			return $data;
		}

		do {
			$slug = wp_generate_password( 11, false );

			// The post_parent will always be 0 but ensures future compat if we go to hierarchical post type with almost no cost.
			$check_sql       = "SELECT post_name FROM %i WHERE post_name = %s AND post_type IN ( %s, 'attachment' ) AND post_parent = %d LIMIT 1";
			$post_name_check = DB::get_var( DB::prepare( $check_sql, DB::prefix( 'posts' ), $slug, static::POSTTYPE, $data['post_parent'] ?? 0 ) );
		} while ( $post_name_check );

		$data['post_name'] = $slug;

		return $data;
	}

	/**
	 * Register custom post type for calendar embeds.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$labels = [
			'name'                     => _x( 'Calendar Embeds', 'post type general name', 'the-events-calendar' ),
			'singular_name'            => _x( 'Calendar Embed', 'post type singular name', 'the-events-calendar' ),
			'add_new'                  => _x( 'Add New', 'calendar embed', 'the-events-calendar' ),
			'add_new_item'             => __( 'Add New Calendar Embed', 'the-events-calendar' ),
			'edit_item'                => __( 'Edit Calendar Embed', 'the-events-calendar' ),
			'new_item'                 => __( 'New Calendar Embed', 'the-events-calendar' ),
			'view_item'                => __( 'View Calendar Embed', 'the-events-calendar' ),
			'search_items'             => __( 'Search Calendar Embeds', 'the-events-calendar' ),
			'not_found'                => __( 'No calendar embeds found.', 'the-events-calendar' ),
			'not_found_in_trash'       => __( 'No calendar embeds found in Trash.', 'the-events-calendar' ),
			'parent_item_colon'        => __( 'Parent Calendar Embeds:', 'the-events-calendar' ),
			'all_items'                => _x( 'Calendar Embeds', 'Label for the calendar embeds list', 'the-events-calendar' ),
			'insert_into_item'         => _x( 'Insert into calendar embed', 'Label for the insert button', 'the-events-calendar' ),
			'uploaded_to_this_item'    => _x( 'Uploaded to this calendar embed', 'Label for the uploaded to this item', 'the-events-calendar' ),
			'menu_name'                => _x( 'Calendar Embeds', 'admin menu', 'the-events-calendar' ),
			'filter_items_list'        => _x( 'Filter calendar embeds list', 'Label for the filter items list', 'the-events-calendar' ),
			'items_list_navigation'    => _x( 'Calendar Embeds list navigation', 'Label for the items list navigation', 'the-events-calendar' ),
			'items_list'               => _x( 'Calendar Embeds list', 'Label for the items list', 'the-events-calendar' ),
			'item_published'           => __( 'Calendar embed published.', 'the-events-calendar' ),
			'item_published_privately' => __( 'Calendar embed published privately.', 'the-events-calendar' ),
			'item_reverted_to_draft'   => __( 'Calendar embed reverted to draft.', 'the-events-calendar' ),
			'item_trashed'             => __( 'Calendar embed trashed.', 'the-events-calendar' ),
			'item_scheduled'           => __( 'Calendar embed scheduled.', 'the-events-calendar' ),
			'item_updated'             => __( 'Calendar embed updated.', 'the-events-calendar' ),
			'item_link'                => _x( 'Calendar embed link', 'Label for the calendar embed link', 'the-events-calendar' ),
			'item_link_description'    => _x( 'A link to the calendar embed.', 'Label for the calendar embed link description', 'the-events-calendar' ),
		];

		$args = [
			'label'              => __( 'Calendar Embeds', 'the-events-calendar' ),
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'calendar-embed' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => [ 'title' ],
			'show_in_rest'       => true,
			'taxonomies'         => [ TEC_Plugin::TAXONOMY, 'post_tag' ],
		];

		/**
		 * Filter the arguments for the Calendar Embeds post type.
		 *
		 * @since 6.11.0
		 *
		 * @param array $args The arguments for the Calendar Embeds post type.
		 *
		 * @return array
		 */
		$args = apply_filters( 'tec_events_calendar_embeds_post_type_args', $args );

		register_post_type( static::POSTTYPE, $args );
	}

	/**
	 * Get the iframe code for the calendar embed.
	 *
	 * @since 6.11.0
	 *
	 * @param int  $post_id                  The post ID.
	 * @param bool $throw_when_not_published Whether to throw an exception if the calendar is not published.
	 *
	 * @return string
	 * @throws NotPublishedCalendarException When the calendar is not published.
	 */
	public static function get_iframe( int $post_id, bool $throw_when_not_published = false ): string {
		$embed = get_post( $post_id );

		if ( ! $embed instanceof WP_Post ) {
			return '';
		}

		if ( static::POSTTYPE !== $embed->post_type ) {
			return '';
		}

		if ( $throw_when_not_published && 'publish' !== $embed->post_status ) {
			throw new NotPublishedCalendarException();
		}

		if ( $throw_when_not_published && post_password_required( $embed ) ) {
			throw new NotPublishedCalendarException();
		}

		$embed_url = 'publish' === $embed->post_status ? get_post_embed_url( $embed ) : get_preview_post_link( $embed, [ 'embed' => 1 ] );

		$iframe_attributes = [
			'frameborder' => '0',
		];

		/**
		 * Filter the iframe attributes for the calendar embed.
		 *
		 * @since 6.11.0
		 *
		 * @param array   $iframe_attributes The iframe attributes.
		 * @param WP_Post $embed             The embed post object.
		 * @param string  $embed_url         The embed URL.
		 *
		 * @return array
		 */
		$iframe_attributes = (array) apply_filters( 'tec_events_calendar_embeds_iframe_attributes', $iframe_attributes, $embed, $embed_url );

		ob_start();
		?>
		<iframe data-tec-events-ece-iframe="true" src="<?php echo esc_url( $embed_url ); ?>" <?php tribe_attributes( $iframe_attributes ); ?>></iframe>
		<?php
		/**
		 * Filter the iframe code for the calendar embed.
		 *
		 * @since 6.11.0
		 *
		 * @param string  $iframe    The iframe code.
		 * @param WP_Post $embed     The embed post object.
		 * @param string  $embed_url The embed URL.
		 *
		 * @return string
		 */
		$iframe = (string) apply_filters( 'tec_events_calendar_embeds_iframe', trim( ob_get_clean() ), $embed, $embed_url );

		/**
		 * Filter the iframe and styles for the calendar embed.
		 *
		 * @since 6.11.0
		 *
		 * @param string  $iframe    The iframe code.
		 * @param WP_Post $embed     The embed post object.
		 * @param string  $embed_url The embed URL.
		 *
		 * @return string
		 */
		return (string) apply_filters( 'tec_events_calendar_embeds_iframe_and_styles', self::print_iframe_styles() . $iframe, $embed, $embed_url );
	}

	/**
	 * Prints the iframe styles.
	 *
	 * @since 6.11.0
	 *
	 * @return string
	 */
	protected static function print_iframe_styles(): string {
		return tribe( Template::class )->template( 'iframe-stylesheet', [], false );
	}
	/**
	 * Get the event categories for a calendar embed.
	 *
	 * @since 6.11.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array
	 */
	public static function get_event_categories( int $post_id ): array {
		$categories = get_the_terms( $post_id, TEC_Plugin::TAXONOMY );

		if ( ! is_array( $categories ) ) {
			return [];
		}

		return array_filter( $categories, static fn ( $c ) => $c instanceof WP_Term );
	}

	/**
	 * Get the event tags for a calendar embed.
	 *
	 * @since 6.11.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return array
	 */
	public static function get_tags( int $post_id ): array {
		$tags = get_the_terms( $post_id, 'post_tag' );

		if ( ! is_array( $tags ) ) {
			return [];
		}

		return array_filter( $tags, static fn ( $t ) => $t instanceof WP_Term );
	}

	/**
	 * Do not add trashed suffix to trashed calendar embeds.
	 *
	 * @since 6.11.0
	 *
	 * @param bool   $add_trashed_suffix Whether to add the trashed suffix.
	 * @param string $post_name          The post name.
	 * @param int    $post_id            The post ID.
	 *
	 * @return bool
	 */
	public function do_not_add_trashed_suffix_to_trashed_calendar_embeds( $add_trashed_suffix, $post_name, $post_id ): bool {
		$add_trashed_suffix = (bool) $add_trashed_suffix;
		$post_id            = (int) $post_id;

		if ( static::POSTTYPE !== get_post_type( $post_id ) ) {
			return $add_trashed_suffix;
		}

		return false;
	}
}
