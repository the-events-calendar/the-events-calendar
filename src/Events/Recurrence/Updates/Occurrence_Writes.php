<?php
/**
 * Preserves occurrence editor identity while persisting shared Event fields.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Updates
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events\Recurrence\Authoring_Guard;
use Tribe__Events__Main as TEC;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * Keeps editor requests on the selected occurrence and routes shared persistence.
 *
 * The occurrence stays the WordPress request and response identity. Shared post
 * fields and relationships use the real Event; date meta retains occurrence scope.
 * Pro advertises its update capability when it can supply single/upcoming/all scope.
 *
 * @since TBD
 */
class Occurrence_Writes {
	/**
	 * Registers the entry-point and write guards.
	 *
	 * @since TBD
	 * @return void
	 */
	public function register(): void {
		add_filter( 'post_row_actions', [ $this, 'row_actions' ], 30, 2 );
		add_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post', [ $this, 'classic_request' ] );
		add_filter( 'rest_request_before_callbacks', [ $this, 'rest_request' ], 5, 3 );
		add_filter( 'query', [ $this, 'route_shared_write' ], 10 );
		add_action( 'clean_post_cache', [ $this, 'clean_shared_cache' ] );
		add_action( 'pre_get_terms', [ $this, 'route_term_query' ] );
		add_filter( 'get_object_terms', [ $this, 'restore_term_object_ids' ], 10, 4 );
		add_action( 'set_object_terms', [ $this, 'terms_changed' ] );
		add_action( 'deleted_term_relationships', [ $this, 'terms_changed' ] );
		add_action( 'clean_object_term_cache', [ $this, 'clean_shared_terms' ], 10, 2 );
		add_filter( 'wp_insert_post_parent', [ $this, 'route_post_parent' ] );
		add_filter( 'wp_unique_post_slug', [ $this, 'shared_slug' ], 10, 6 );
		foreach ( [ 'pre_trash_post', 'pre_untrash_post', 'pre_delete_post' ] as $hook ) {
			add_filter( $hook, [ $this, 'reject_delete' ], 10, 2 );
		}
	}

	/**
	 * Removes the entry-point and write guards.
	 *
	 * @since TBD
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'post_row_actions', [ $this, 'row_actions' ], 30 );
		remove_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post', [ $this, 'classic_request' ] );
		remove_filter( 'rest_request_before_callbacks', [ $this, 'rest_request' ], 5 );
		remove_filter( 'query', [ $this, 'route_shared_write' ], 10 );
		remove_action( 'clean_post_cache', [ $this, 'clean_shared_cache' ] );
		remove_action( 'pre_get_terms', [ $this, 'route_term_query' ] );
		remove_filter( 'get_object_terms', [ $this, 'restore_term_object_ids' ] );
		remove_action( 'set_object_terms', [ $this, 'terms_changed' ] );
		remove_action( 'deleted_term_relationships', [ $this, 'terms_changed' ] );
		remove_action( 'clean_object_term_cache', [ $this, 'clean_shared_terms' ] );
		remove_filter( 'wp_insert_post_parent', [ $this, 'route_post_parent' ] );
		remove_filter( 'wp_unique_post_slug', [ $this, 'shared_slug' ] );
		foreach ( [ 'pre_trash_post', 'pre_untrash_post', 'pre_delete_post' ] as $hook ) {
			remove_filter( $hook, [ $this, 'reject_delete' ] );
		}
	}

	/**
	 * Labels the shared editing scope and removes unsupported occurrence actions.
	 *
	 * @since TBD
	 * @param array   $actions The row actions.
	 * @param WP_Post $post    The row post.
	 * @return array The supported actions.
	 */
	public function row_actions( array $actions, WP_Post $post ): array {
		$parent = $this->parent_id( (int) $post->ID );
		if ( ! $parent ) {
			return $actions;
		}
		unset( $actions['trash'], $actions['delete'], $actions['untrash'], $actions['inline hide-if-no-js'] );
		if ( isset( $actions['edit'] ) && current_user_can( 'edit_post', $parent ) ) {
			$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'Edit occurrence', 'the-events-calendar' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Allows occurrence editor requests, refusing unsupported destructive operations.
	 *
	 * WordPress retains the occurrence ID for nonces, capabilities, date hooks and
	 * the save redirect. Only shared persistence is routed to the Event.
	 *
	 * @since TBD
	 * @return void
	 */
	public function classic_request(): void {
		$id     = absint( tribe_get_request_var( 'post_ID', tribe_get_request_var( 'post', 0 ) ) );
		$parent = $this->parent_id( $id );
		if ( ! $parent ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $parent ) ) {
			wp_die( esc_html__( 'You are not allowed to edit this event.', 'the-events-calendar' ), 403 );
			return;
		}
		if ( ! in_array( tribe_get_request_var( 'action', '' ), [ 'edit', 'editpost' ], true ) ) {
			wp_die( esc_html__( 'Removing or restoring an individual occurrence is not supported here. This occurrence was not changed.', 'the-events-calendar' ), 409 );
			return;
		}
		if ( 'edit' === tribe_get_request_var( 'action', '' ) ) {
			tribe( Admin_Notice::class )->set( 'info', __( 'You are editing this occurrence. Content, status and categories are shared by every date of this event. Date changes apply only to this occurrence when its schedule is editable.', 'the-events-calendar' ) );
		}
	}

	/**
	 * Rejects destructive REST operations without changing the occurrence identity.
	 *
	 * @since TBD
	 * @param mixed           $response The existing response.
	 * @param mixed           $handler  The selected handler.
	 * @param WP_REST_Request $request  The request.
	 * @return mixed The existing response or a scope error.
	 */
	public function rest_request( $response, $handler, WP_REST_Request $request ) {
		if ( null !== $response || in_array( $request->get_method(), [ 'GET', 'HEAD', 'OPTIONS' ], true ) ) {
			return $response;
		}
		$type      = get_post_type_object( TEC::POSTTYPE );
		$base      = $type->rest_base ?: TEC::POSTTYPE;
		$namespace = $type->rest_namespace ?: 'wp/v2';
		if ( ! preg_match( '#^/' . preg_quote( $namespace . '/' . $base, '#' ) . '/(\d+)(?:/|$)#', $request->get_route(), $matches ) ) {
			return $response;
		}
		$parent = $this->parent_id( (int) $matches[1] );
		if ( ! $parent || 'DELETE' !== $request->get_method() ) {
			return $response;
		}
		return new WP_Error( 'tec_occurrence_edit_scope', __( 'Removing or restoring an individual occurrence is not supported here. This occurrence was not changed.', 'the-events-calendar' ), [ 'status' => 409 ] );
	}

	/**
	 * Prevents trash, restore and deletion from reporting a synthetic post as saved.
	 *
	 * @since TBD
	 * @param mixed   $check An earlier short-circuit result.
	 * @param WP_Post $post  The target post.
	 * @return mixed False for unsupported occurrence operations.
	 */
	public function reject_delete( $check, WP_Post $post ) {
		return $this->parent_id( (int) $post->ID ) ? false : $check;
	}

	/**
	 * Routes WordPress's shared post and taxonomy SQL to the durable Event.
	 *
	 * wp_insert_post has no filter for its UPDATE target. Keep its occurrence ID
	 * through capability checks, meta writes, save hooks and REST responses, and
	 * replace only the primary-key predicate of its single-post UPDATE. Never
	 * rewrite post DELETEs or IDs appearing in user content. Taxonomy relationships
	 * similarly belong to the Event; match only WordPress's relationship queries.
	 *
	 * @since TBD
	 * @param string $sql The database query.
	 * @return string The query with its shared storage target resolved.
	 */
	public function route_shared_write( string $sql ): string {
		global $wpdb;
		$posts = preg_quote( $wpdb->posts, '#' );
		$terms = preg_quote( $wpdb->term_relationships, '#' );
		$patterns = [
			"#^UPDATE `?{$posts}`? SET .* WHERE `?ID`? = (\\d+)$#s",
			"#^(?:SELECT term_taxonomy_id FROM|DELETE FROM) `?{$terms}`? WHERE object_id = (\\d+) AND #",
			"#^INSERT INTO `?{$terms}`? \\(\s*`?object_id`?,\s*`?term_taxonomy_id`?\\) VALUES \\((\\d+),#",
		];
		foreach ( $patterns as $pattern ) {
			if ( ! preg_match( $pattern, $sql, $matches, PREG_OFFSET_CAPTURE ) ) {
				continue;
			}
			$id = (int) $matches[1][0];
			$parent = $this->parent_id( $id );
			if ( $parent ) {
				return substr_replace( $sql, (string) $parent, $matches[1][1], strlen( $matches[1][0] ) );
			}
		}
		return $sql;
	}

	/**
	 * Invalidates the Event and sibling occurrence caches after a shared write.
	 *
	 * @since TBD
	 * @param int $post_id The post whose cache WordPress cleared.
	 * @return void
	 */
	public function clean_shared_cache( int $post_id ): void {
		$parent = $this->parent_id( $post_id );
		if ( ! $parent ) {
			return;
		}
		tribe( Provisional_Post::class )->get_post_cache()->flush_occurrences( $parent );
		clean_post_cache( $parent );
	}

	/**
	 * Resolves shared taxonomy reads, including the old terms used during a save.
	 *
	 * @since TBD
	 * @param \WP_Term_Query $query The term query.
	 * @return void
	 */
	public function route_term_query( $query ): void {
		if ( empty( $query->query_vars['object_ids'] ) ) {
			return;
		}
		$query->query_vars['object_ids'] = array_map( [ $this, 'route_post_parent' ], wp_parse_id_list( $query->query_vars['object_ids'] ) );
	}

	/**
	 * Keeps term-cache hydration keyed by the occurrence IDs that were requested.
	 *
	 * WordPress uses all_with_object_id to prime several posts at once. Returning
	 * the storage Event ID would populate the wrong cache and hide selected terms.
	 * Clone shared terms for each requested occurrence without mutating term caches.
	 *
	 * @since TBD
	 * @param mixed $terms The retrieved terms.
	 * @param int[] $ids The requested object IDs.
	 * @param string[] $taxonomies The requested taxonomies.
	 * @param array $args The query arguments.
	 * @return mixed The terms with their requested object identities.
	 */
	public function restore_term_object_ids( $terms, array $ids, array $taxonomies, array $args ) {
		if ( tribe( Authoring_Guard::class )->has_external_updates() || ! is_array( $terms ) || 'all_with_object_id' !== ( $args['fields'] ?? '' ) ) {
			return $terms;
		}
		$owners = [];
		foreach ( $ids as $id ) {
			$owners[ $this->route_post_parent( (int) $id ) ][] = $id;
		}
		$result = [];
		foreach ( $terms as $term ) {
			foreach ( $owners[ $term->object_id ] ?? [ $term->object_id ] as $id ) {
				$copy = clone $term;
				$copy->object_id = $id;
				$result[] = $copy;
			}
		}
		return $result;
	}

	/**
	 * Clears shared taxonomy caches after WordPress finishes an occurrence save.
	 *
	 * @since TBD
	 * @param int[] $ids The updated object IDs.
	 * @param string $type The post type.
	 * @return void
	 */
	public function clean_shared_terms( array $ids, string $type ): void {
		foreach ( $ids as $id ) {
			$parent = $this->parent_id( (int) $id );
			if ( $parent ) {
				clean_object_term_cache( $parent, $type );
			}
		}
	}

	/**
	 * Invalidates shared taxonomy state after relationship writes.
	 *
	 * @since TBD
	 * @param int $id The object whose terms changed.
	 * @return void
	 */
	public function terms_changed( int $id ): void {
		$parent = $this->parent_id( $id );
		if ( ! $parent ) {
			return;
		}
		clean_object_term_cache( $parent, TEC::POSTTYPE );
		foreach ( Occurrence::where( 'post_id', '=', $parent )->all() as $occurrence ) {
			clean_object_term_cache( (int) $occurrence->provisional_id, TEC::POSTTYPE );
		}
	}

	/**
	 * Gives revisions and attachments a durable parent.
	 *
	 * @since TBD
	 * @param int $id The proposed parent.
	 * @return int The Event ID, or the original parent.
	 */
	public function route_post_parent( int $id ): int {
		return $this->parent_id( $id ) ?: $id;
	}

	/**
	 * Checks shared slug uniqueness against the Event instead of its occurrence.
	 *
	 * @since TBD
	 * @param string $slug The generated slug.
	 * @param int $id The post ID.
	 * @param string $status The post status.
	 * @param string $type The post type.
	 * @param int $post_parent The hierarchical parent.
	 * @param string $original The submitted slug.
	 * @return string The shared slug.
	 */
	public function shared_slug( string $slug, int $id, string $status, string $type, int $post_parent, string $original ): string {
		$parent = $this->parent_id( $id );
		return $parent ? wp_unique_post_slug( $original, $parent, $status, $type, $post_parent ) : $slug;
	}

	/**
	 * Resolves a Free-owned provisional post to its durable Event.
	 *
	 * @since TBD
	 * @param int $id The candidate post ID.
	 * @return int The parent post ID, or zero when the guard does not apply.
	 */
	private function parent_id( int $id ): int {
		if ( tribe( Authoring_Guard::class )->has_external_updates() || ! tribe( Provisional_Post::class )->is_provisional_post_id( $id ) ) {
			return 0;
		}
		$parent = Occurrence::normalize_id( $id );
		return $parent !== $id && TEC::POSTTYPE === get_post_type( $parent ) ? $parent : 0;
	}
}
