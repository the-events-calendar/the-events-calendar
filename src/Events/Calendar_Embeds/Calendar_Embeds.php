<?php
/**
 * External Calendar Embeds Controller.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use WP_Post;
use Tribe__Events__Main as TEC_Plugin;
use WP_Term;
use TEC\Common\StellarWP\DB\DB;

/**
 * Class Calendar_Embeds
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */
class Calendar_Embeds extends Controller_Contract {

	/**
	 * Calendar Embeds post type slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	const POSTTYPE = 'tec_calendar_embed';

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function do_register(): void {
		add_action( 'init', [ $this, 'register_post_type' ], 15 );
		add_action( 'tribe_events_views_v2_before_make_view_for_rest', [ Render::class, 'maybe_toggle_hooks_for_rest' ], 10, 2 );
		add_filter( 'wp_insert_post_data', [ $this, 'disable_slug_changes' ], 10, 4 );
		add_filter( 'get_terms', [ $this, 'modify_term_count_on_term_list_table' ], 10, 2 );
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_action( 'init', [ $this, 'register_post_type' ], 15 );
		remove_action( 'tribe_events_views_v2_before_make_view_for_rest', [ Render::class, 'maybe_toggle_hooks_for_rest' ] );
		remove_filter( 'wp_insert_post_data', [ $this, 'disable_slug_changes' ] );
		remove_filter( 'get_terms', [ $this, 'modify_term_count_on_term_list_table' ] );
	}

	/**
	 * Modifies the term count on the term list tables to ignore Calendar embeds from their count.
	 *
	 * @since TBD
	 *
	 * @param array $terms      The terms.
	 * @param array $taxonomies The taxonomies.
	 *
	 * @return array
	 */
	public function modify_term_count_on_term_list_table( array $terms, array $taxonomies ): array {
		if ( ! in_array( TEC_Plugin::TAXONOMY, $taxonomies, true ) && ! in_array( 'post_tag', $taxonomies, true ) ) {
			return $terms;
		}

		if ( ! is_admin() ) {
			// Should only run on BE.
			return $terms;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
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
	 * @since TBD
	 *
	 * @param array $data              The post data.
	 * @param array $post_array        The post array.
	 * @param array $unsafe_post_array The unsanitized post array.
	 * @param bool  $update            Whether the post is being updated.
	 *
	 * @return array
	 */
	public function disable_slug_changes( array $data, array $post_array, array $unsafe_post_array, bool $update ): array {
		if ( static::POSTTYPE !== $data['post_type'] ) {
			return $data;
		}

		if ( $update ) {
			// Ensure the post name is not updated.
			$data['post_name'] = get_post( $post_array['ID'] )->post_name;

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
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		$labels = [
			'name'               => _x( 'Calendar Embeds', 'post type general name', 'the-events-calendar' ),
			'singular_name'      => _x( 'Calendar Embed', 'post type singular name', 'the-events-calendar' ),
			'menu_name'          => _x( 'Calendar Embeds', 'admin menu', 'the-events-calendar' ),
			'name_admin_bar'     => _x( 'Calendar Embed', 'add new on admin bar', 'the-events-calendar' ),
			'add_new'            => _x( 'Add New', 'calendar embed', 'the-events-calendar' ),
			'add_new_item'       => __( 'Add New Calendar Embed', 'the-events-calendar' ),
			'new_item'           => __( 'New Calendar Embed', 'the-events-calendar' ),
			'edit_item'          => __( 'Edit Calendar Embed', 'the-events-calendar' ),
			'view_item'          => __( 'View Calendar Embed', 'the-events-calendar' ),
			'all_items'          => __( 'Calendar Embeds', 'the-events-calendar' ),
			'search_items'       => __( 'Search Calendar Embeds', 'the-events-calendar' ),
			'parent_item_colon'  => __( 'Parent Calendar Embeds:', 'the-events-calendar' ),
			'not_found'          => __( 'No calendar embeds found.', 'the-events-calendar' ),
			'not_found_in_trash' => __( 'No calendar embeds found in Trash.', 'the-events-calendar' ),
		];

		$args = [
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
		 * @since TBD
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
	 * @since TBD
	 *
	 * @param int $post_id The post ID.
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

		$embed_url = 'publish' === $embed->post_status ? get_post_embed_url( $embed ) : get_preview_post_link( $embed, [ 'embed' => 1 ] );

		$iframe = '<iframe src="' . esc_url( $embed_url ) . '" width="100%" height="600" style="max-width:100%;" frameborder="0"></iframe>';

		/**
		 * Filter the iframe code for the calendar embed.
		 *
		 * @since TBD
		 *
		 * @param string  $iframe    The iframe code.
		 * @param WP_Post $embed     The embed post object.
		 * @param string  $embed_url The embed URL.
		 *
		 * @return string
		 */
		return (string) apply_filters( 'tec_events_calendar_embeds_iframe', $iframe, $embed, $embed_url );
	}

	/**
	 * Get the event categories for a calendar embed.
	 *
	 * @since TBD
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
	 * @since TBD
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
}
