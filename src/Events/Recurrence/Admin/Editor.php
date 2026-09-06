<?php
/**
 * Keeps event identity and recurrence availability visible in both editors.
 *
 * @since TBD
 * @package TEC\Events\Recurrence\Admin
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Admin;

use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request;

/** Persistent editor context, separate from dismissible save notifications. @since TBD */
class Editor {
	/** @var string Read-only, edit-context response field, refreshed after saves. @since TBD */
	public const FIELD = 'tec_recurrence_admin';

	/** Registers editor configuration and rendering. @since TBD @return void */
	public function register(): void {
		add_filter( 'tribe_editor_config', [ $this, 'config' ], 100 );
		add_action( 'edit_form_after_title', [ $this, 'render' ] );
		if ( did_action( 'init' ) ) {
			$this->register_field();
		} else {
			add_action( 'init', [ $this, 'register_field' ] );
		}
	}

	/** Removes callbacks and the editor-only response field. @since TBD @return void */
	public function unregister(): void {
		remove_filter( 'tribe_editor_config', [ $this, 'config' ], 100 );
		remove_action( 'edit_form_after_title', [ $this, 'render' ] );
		remove_action( 'init', [ $this, 'register_field' ] );
		// WordPress has no unregister_rest_field API; remove only the field owned here.
		global $wp_rest_additional_fields;
		unset( $wp_rest_additional_fields[ TEC::POSTTYPE ][ self::FIELD ] );
	}

	/**
	 * Registers read-only context on the existing post response, never on public responses.
	 * The saved response refreshes classification after adding/removing dates or changing Pro rules.
	 *
	 * @since TBD
	 * @return void
	 */
	public function register_field(): void {
		register_rest_field(
			TEC::POSTTYPE,
			self::FIELD,
			[
				'get_callback' => [ $this, 'rest_data' ],
				'schema'       => [
					'type'     => 'object',
					'context'  => [ 'edit' ],
					'readonly' => true,
				],
			]
		);
	}

	/**
	 * Returns editor context only to someone allowed to edit the requested identity.
	 *
	 * @since TBD
	 * @param array           $post_data  Serialized post.
	 * @param string          $field   Additional field name.
	 * @param WP_REST_Request $request Current request.
	 * @return array Editor context, or nothing outside authorized edit requests.
	 */
	public function rest_data( array $post_data, string $field, WP_REST_Request $request ): array {
		$id = (int) $post_data['id'];
		if ( 'edit' !== $request->get_param( 'context' ) || ! current_user_can( 'edit_post', $id ) ) {
			return [];
		}
		tribe( Presentation::class )->reset();
		return $this->data( $id );
	}

	/**
	 * Adds initial context independently of Pro's dates-authoring configuration.
	 *
	 * @since TBD
	 * @param mixed $config Existing editor configuration.
	 * @return mixed Configuration with recurrence context on event editors.
	 */
	public function config( $config ) {
		if ( ! is_array( $config ) ) {
			return $config;
		}
		global $post;
		$id = $post instanceof WP_Post ? (int) $post->ID : absint( tribe_get_request_var( 'post', 0 ) );
		if ( $id && TEC::POSTTYPE === get_post_type( $id ) && current_user_can( 'edit_post', $id ) ) {
			$config['events']['recurrenceAdmin'] = $this->data( $id );
		}
		return $config;
	}

	/**
	 * Builds the shared editor explanation without changing authoring scope.
	 *
	 * @since TBD
	 * @param int $id Editor identity.
	 * @return array Shared display data.
	 */
	public function data( int $id ): array {
		$data            = tribe( Presentation::class )->get( $id );
		$data['heading'] = $data['isOccurrence'] ? __( 'Editing occurrence', 'the-events-calendar' ) : __( 'Editing event', 'the-events-calendar' );
		$data['status']  = tribe( Pro_Status::class )->get( 'rules' === $data['schedule'] );
		if ( $data['externalScope'] && 'single' !== $data['schedule'] ) {
			$data['scope'] = __( 'Use the recurrence scope controls to choose which dates your changes affect.', 'the-events-calendar' );
		} elseif ( $data['isOccurrence'] ) {
			$data['scope'] = $data['locked']
				? __( 'Content, status and categories are shared by every date of this event. This occurrence’s dates are locked while recurrence editing is unavailable.', 'the-events-calendar' )
				: __( 'Content, status and categories are shared by every date of this event. Changing the start or end date moves only this occurrence. All Day applies to every date.', 'the-events-calendar' );
		} elseif ( 'single' !== $data['schedule'] ) {
			$data['scope'] = __( 'You are editing the event’s shared details. These details apply to all of its scheduled dates.', 'the-events-calendar' );
		} else {
			$data['scope'] = $data['count'] ? __( 'This event has one scheduled date.', 'the-events-calendar' ) : __( 'This event has no scheduled dates yet.', 'the-events-calendar' );
		}
		return $data;
	}

	/**
	 * Renders persistent Classic Editor context immediately beneath the title.
	 *
	 * @since TBD
	 * @param WP_Post $post Edited post.
	 * @return void
	 */
	public function render( WP_Post $post ): void {
		if ( TEC::POSTTYPE !== $post->post_type || ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		$data = $this->data( $post->ID );
		echo '<section class="tec-occurrence-admin__editor" aria-label="' . esc_attr__( 'Event editing context', 'the-events-calendar' ) . '"><p><strong>' . esc_html( $data['heading'] ) . '</strong> · ' . esc_html( $data['scheduleLabel'] ) . '</p><p>' . esc_html( $data['start'] ) . ' — ' . esc_html( $data['end'] ) . '</p><p>' . esc_html( $data['scope'] ) . '</p>';
		if ( $data['isOccurrence'] && $data['parentEditLink'] ) {
			echo '<p><a href="' . esc_url( $data['parentEditLink'] ) . '">' . esc_html__( 'Edit event details', 'the-events-calendar' ) . '</a> · <a href="' . esc_url( $data['datesLink'] ) . '">' . esc_html__( 'View all dates', 'the-events-calendar' ) . '</a></p>';
		}
		if ( $data['locked'] ) {
			$status = $data['status'];
			echo '<p class="description">';
			if ( $status['url'] ) {
				echo '<a href="' . esc_url( $status['url'] ) . '" title="' . esc_attr( $status['title'] ) . '">' . esc_html( $status['label'] ) . '</a>';
			} else {
				echo esc_html( $status['guidance'] );
			}
			echo '</p>';
		}
		echo '</section>';
	}
}
