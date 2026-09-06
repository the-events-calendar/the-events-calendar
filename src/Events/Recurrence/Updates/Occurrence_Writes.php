<?php
/**
 * Keeps Free WordPress editing on the durable Event post.
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
 * Routes editor entry points to the Event and rejects unsupported occurrence writes.
 *
 * Additional dates are authored in the Event editor. WordPress content, status,
 * taxonomy, revision and attachment operations consequently use the real post.
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
		add_filter( 'get_edit_post_link', [ $this, 'edit_link' ], 20, 3 );
		add_filter( 'post_row_actions', [ $this, 'row_actions' ], 30, 2 );
		add_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post', [ $this, 'classic_request' ] );
		add_filter( 'rest_request_before_callbacks', [ $this, 'rest_request' ], 5, 3 );
		add_filter( 'wp_insert_post_empty_content', [ $this, 'reject_post_write' ], 10, 2 );
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
		remove_filter( 'get_edit_post_link', [ $this, 'edit_link' ], 20 );
		remove_filter( 'post_row_actions', [ $this, 'row_actions' ], 30 );
		remove_action( 'tec_events_custom_tables_v1_redirect_classic_editor_event_post', [ $this, 'classic_request' ] );
		remove_filter( 'rest_request_before_callbacks', [ $this, 'rest_request' ], 5 );
		remove_filter( 'wp_insert_post_empty_content', [ $this, 'reject_post_write' ] );
		foreach ( [ 'pre_trash_post', 'pre_untrash_post', 'pre_delete_post' ] as $hook ) {
			remove_filter( $hook, [ $this, 'reject_delete' ] );
		}
	}

	/**
	 * Points an occurrence edit link at the Event editor.
	 *
	 * @since TBD
	 * @param string $link    The edit link.
	 * @param int    $post_id The post ID.
	 * @param string $context The link context.
	 * @return string The Event edit link, when applicable.
	 */
	public function edit_link( $link, $post_id, $context ): string {
		if ( tribe( Authoring_Guard::class )->has_external_updates() ) {
			return $link;
		}
		$parent = $this->parent_id( (int) $post_id ) ?: (int) $post_id;
		return TEC::POSTTYPE === get_post_type( $parent ) ? $this->parent_edit_link( $parent, $context ) : $link;
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
			$actions['edit'] = '<a href="' . esc_url( $this->parent_edit_link( $parent ) ) . '">' . esc_html__( 'Edit event and dates', 'the-events-calendar' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Redirects old editor links, refusing already-submitted occurrence operations.
	 *
	 * No POST payload is replayed against the Event: that would silently broaden the
	 * user's requested scope. The parent editor performs WordPress's normal checks.
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
		if ( 'GET' !== sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) || 'edit' !== tribe_get_request_var( 'action', '' ) ) {
			wp_die( esc_html__( 'Edit the event to change its content, status or scheduled dates. This occurrence was not changed.', 'the-events-calendar' ), 409 );
			return;
		}
		tribe( Admin_Notice::class )->set( 'info', __( 'You are editing the event. Content and status apply to all its dates; additional dates can be changed in Event Dates.', 'the-events-calendar' ) );
		wp_safe_redirect( $this->parent_edit_link( $parent, 'raw' ) );
		tribe_exit();
	}

	/**
	 * Rejects REST mutations of provisional posts with an explicit scope error.
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
		if ( ! $parent ) {
			return $response;
		}
		return new WP_Error( 'tec_occurrence_edit_scope', __( 'Edit the event to change its content, status or scheduled dates. This occurrence was not changed.', 'the-events-calendar' ), [ 'status' => 409 ] );
	}

	/**
	 * Makes programmatic WordPress post writes fail before any data is modified.
	 *
	 * @since TBD
	 * @param bool  $reject   Whether the post should be rejected.
	 * @param array $postarr The proposed post data.
	 * @return bool Whether to reject the write.
	 */
	public function reject_post_write( bool $reject, array $postarr ): bool {
		return $reject || (bool) $this->parent_id( (int) ( $postarr['ID'] ?? 0 ) );
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
	 * Builds a durable Event edit URL without re-entering occurrence link filters.
	 *
	 * The CT1 edit-link filter maps a recurring parent to its first occurrence in
	 * wp-admin. Calling get_edit_post_link here would redirect an occurrence to itself.
	 * Preserve WordPress's edit template, capability check and context escaping.
	 *
	 * @since TBD
	 * @param int    $parent_id  The durable Event post ID.
	 * @param string $context The WordPress edit-link context.
	 * @return string The parent edit URL, or an empty string when unavailable.
	 */
	private function parent_edit_link( int $parent_id, string $context = 'display' ): string {
		$type = get_post_type_object( TEC::POSTTYPE );
		if ( ! $type || ! $type->_edit_link || ! current_user_can( 'edit_post', $parent_id ) ) {
			return '';
		}
		$action = 'display' === $context ? '&amp;action=edit' : '&action=edit';
		return admin_url( sprintf( $type->_edit_link . $action, $parent_id ) );
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
