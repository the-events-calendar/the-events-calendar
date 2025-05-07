<?php
/**
 * The main Classy feature controller for The Events Calendar.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Events__Main as TEC;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends Controller_Contract {
	/**
	 * The list of single event meta keys to be registered.
	 *
	 * @var array<string>
	 */
	private const SINGLE_META = [
		TEC::POSTTYPE => [
			'_EventURL',
			'_EventStartDate',
			'_EventEndDate',
			'_EventAllDay',
			'_EventTimezone',
		],
	];

	/**
	 * The list of multiple event meta keys to be registered.
	 *
	 * @var array<string>
	 */
	const MULTIPLE_META = [
		TEC::POSTTYPE => [
			'_EventOrganizerID',
			'_EventVenueID',
		],
	];

	/**
	 * Registers the hooks and filters for this controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->register_meta_fields();
	}

	/**
	 * Unregisters the hooks and filters added by this controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->unregister_meta_fields();
	}

	/**
	 * Registers meta fields for events.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function register_meta_fields(): void {
		foreach ( self::SINGLE_META as $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {

				register_post_meta(
					TEC::POSTTYPE,
					$meta_key,
					[
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => static function () {
							return current_user_can( 'edit_posts' );
						},
					]
				);
			}
		}

		foreach ( self::MULTIPLE_META as $post_type => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
				register_post_meta(
					TEC::POSTTYPE,
					$meta_key,
					[
						'show_in_rest'  => true,
						'single'        => false,
						'type'          => 'integer',
						'auth_callback' => static function () {
							return current_user_can( 'edit_posts' );
						},
					]
				);
			}
		}
	}

	/**
	 * Unregisters the post meta fields for the plugin.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function unregister_post_meta(): void {
		foreach ( self::SINGLE_META as $post_type => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
				unregister_post_meta( $post_type, $meta_key );
			}
		}

		foreach ( self::MULTIPLE_META as $post_type => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
				unregister_post_meta( $post_type, $meta_key );
			}
		}
	}
}
