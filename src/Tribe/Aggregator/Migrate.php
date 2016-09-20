<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Migrate {
	/**
 	 * Static Singleton Holder
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		return self::$instance ? self::$instance : self::$instance = new self;
	}

	/**
	 * Setup all the hooks and filters
	 *
	 * @return void
	 */
	private function __construct() {
		$plugin = Tribe__Events__Main::instance();

		// Hook the AJAX methods
		add_action( 'wp_ajax_tribe_convert_legacy_facebook_settings', array( $this, 'ajax_convert_facebook_settings' ) );
		add_action( 'wp_ajax_tribe_convert_legacy_ical_settings', array( $this, 'ajax_convert_ical_settings' ) );

		// Hook the Notice for the Migration
		tribe_notice( 'tribe-aggregator-migrate-legacy-settings', array( $this, 'notice' ), 'type=warning' );

		// Register Assets
		tribe_asset( $plugin, 'tribe-migrate-legacy-settings', 'aggregator-admin-legacy-settings.js', array( 'jquery' ), 'admin_enqueue_scripts' );
	}

	/**
	 * Checks if there are existing settings from the Old iCal or Facebook Plugins
	 * and displays a notice with a button to migrated those using AJAX
	 *
	 * @return string
	 */
	public function notice() {
		if ( ! Tribe__Admin__Helpers::instance()->is_screen() ) {
			return false;
		}

		if (
			( $this->is_facebook_migrated() || ! $this->has_facebook_setting() ) &&
			( $this->is_ical_migrated() || ! $this->has_ical_setting() )
		) {
			return false;
		}

		$aggregator = Tribe__Events__Aggregator::instance();

		$html = '<p>' . esc_html__( 'Seems like you have old settings from before the Event Aggregator existed, the buttons bellow will allow you to migrate these legacy settings to Aggregator Records', 'the-events-calendar' );

		if ( ! $this->is_facebook_migrated() && $this->has_facebook_setting() ) {
			$html .= '<p style="display:inline-block;">' . get_submit_button( esc_html__( 'Migrate Facebook settings' ), 'secondary', 'tribe-migrate-facebook-settings', false ) . '<span class="spinner"></span></p>';
		}

		if ( ! $this->is_ical_migrated() && $this->has_ical_setting() ) {
			$html .= '<p style="display:inline-block;">' . get_submit_button( esc_html__( 'Migrate iCal Settings' ), 'secondary', 'tribe-migrate-ical-settings', false ) . '<span class="spinner"></span></p>';
		}

		return Tribe__Admin__Notices::instance()->render( 'tribe-aggregator-migrate-legacy-settings', $html );
	}

	/**
	 * Gets one or all the Facebook legacy settings
	 *
	 * @param string|null $index If null will return a Object with all the legacy settings
	 *
	 * @return mixed
	 */
	public function get_facebook_setting( $index = null ) {
		// It's important only to use values here that are true for the `empty()` function
		$data = (object) array(
			'post_status' => null,
			'ids' => '',
			'google_maps' => false,
			'auto' => false,
			'frequency' => null,
		);

		$post_status = tribe_get_option( 'imported_post_status', $data->post_status );
		if ( ! empty( $post_status['facebook'] ) ) {
			$data->post_status = $post_status['facebook'];
		}

		$ids = tribe_get_option( 'fb_uids', $data->ids );
		if ( ! empty( $ids ) ) {
			// Clean and Break into multiple Items
			$ids = str_replace( "\r", '', $ids );
			$ids = array_unique( array_filter( explode( "\n" , $ids ) ) );
			$ids = array_map( 'trim',  $ids );

			foreach ( $ids as $id ) {
				if ( is_numeric( $id ) ) {
					$data->ids[] = 'https://www.facebook.com/events/' . $id;
				} elseif ( false === strpos( $id, 'https://www.facebook.com/' ) ) {
					$data->ids[] = 'https://www.facebook.com/' . $id;
				} else {
					$data->ids[] = $id;
				}
			}
		}

		$data->google_maps = (bool) tribe_get_option( 'fb_enable_GoogleMaps', $data->google_maps );
		$data->auto = (bool) tribe_get_option( 'fb_auto_import', $data->auto );
		$frequency = tribe_get_option( 'fb_auto_frequency', $data->frequency );
		if ( ! empty( $frequency ) ) {
			$data->frequency = $frequency;
		}

		if ( ! is_null( $index )  ) {
			return isset( $data->$index ) ? $data->$index : null;
		}

		return $data;
	}

	/**
	 * Checks if one or any Facebook settings exists
	 *
	 * @param string|null $index If null will return a Object with all the legacy settings
	 *
	 * @return bool
	 */
	public function has_facebook_setting( $index = null ) {
		// Fetches the Settings, and remove the empty Ones
		$values = array_filter( (array) $this->get_facebook_setting( $index ), array( $this, 'is_empty' ) );

		// if it's empty means we have empty legacy settings
		return ! empty( $values );
	}

	/**
	 * Checks if legacy Facebook settings were migrated
	 *
	 * @return bool
	 */
	public function is_facebook_migrated() {
		$args = array(
			'post_status' => Tribe__Events__Aggregator__Records::$status->schedule,
			'posts_per_page' => 1,
			'post_mime_type' => 'ea/facebook',
		);

		return Tribe__Events__Aggregator__Records::instance()->query( $args )->have_posts();
	}

	/**
	 * Gets one or all the iCal legacy settings
	 *
	 * @param string|null $index If null will return a Object with all the legacy settings
	 *
	 * @return mixed
	 */
	public function get_ical_setting() {
		return (object) array();
	}

	/**
	 * Checks if one or any iCal settings exists
	 *
	 * @param string|null $index If null will return a Object with all the legacy settings
	 *
	 * @return bool
	 */
	public function has_ical_setting( $index = null ) {
		// Fetches the Settings, and remove the empty Ones
		$values = array_filter( (array) $this->get_ical_setting( $index ), array( $this, 'is_empty' ) );

		// if it's empty means we have empty legacy settings
		return ! empty( $values );
	}

	/**
	 * Checks if legacy Facebook settings were migrated
	 *
	 * @return bool
	 */
	public function is_ical_migrated() {
		$args = array(
			'post_status' => Tribe__Events__Aggregator__Records::$status->schedule,
			'posts_per_page' => 1,
			'post_mime_type' => 'ea/ical',
		);

		return Tribe__Events__Aggregator__Records::instance()->query( $args )->have_posts();
	}

	/**
	 * Method that Handles the AJAX converting of Legacy Facebook Settings
	 * AJAX methods will not return anything, only print a JSON string
	 *
	 * @return void
	 */
	public function ajax_convert_facebook_settings() {
		$response = (object) array(
			'status' => false,
			'text' => esc_html__( 'Error, a unknown bug happened and it was impossible to migrate the old Facebook Settings to Event Aggregator, try again later.', 'the-events-calendar' ),
		);

		$post_type = get_post_type_object( Tribe__Events__Main::POSTTYPE );

		if ( empty( $post_type->cap->edit_posts ) || ! current_user_can( $post_type->cap->edit_posts ) ) {
			$response->status = false;
			$response->text = esc_html__( 'You do not have permission to migrate the old Facebook Settings to Event Aggregator', 'the-events-calendar' );

			wp_send_json( $response );
		}

		if ( ! $this->has_facebook_setting() ) {
			$response->status = false;
			$response->text = esc_html__( 'It does not seem like there are any Legacy Facebook settings to be imported', 'the-events-calendar' );

			wp_send_json( $response );
		}

		$settings = $this->get_facebook_setting();

		$status = (object) array(
			'error' => array(),
			'success' => array(),
		);

		$origin = 'facebook';

		foreach ( $settings->ids as $source ) {
			$record = Tribe__Events__Aggregator__Records::instance()->get_by_origin( $origin );

			/**
			 * @todo Include the Deactivated logic
			 */
			$meta = array(
				'origin'       => $origin,
				'type'         => 'schedule',
				'frequency'    => $settings->frequency,
				'file'         => null,
				'keywords'     => null,
				'location'     => null,
				'start'        => null,
				'radius'       => null,
				'source'       => $source,
				'content_type' => null,
				'is_legacy'    => true,
				'import_id'    => null,
				'post_status' => $settings->post_status,
			);

			$post = $record->create( 'schedule', array(), $meta );

			if ( is_wp_error( $post ) ) {
				$status->error[] = $post;
			} else {
				$status->success[] = $post->id;

				// Update status from Draft to Schedule
				$args['ID'] = absint( $post->id );
				$args['post_status'] = Tribe__Events__Aggregator__Records::$status->schedule;
				wp_update_post( $args );
			}
		}

		$response->status = true;
		$response->text = esc_html__( 'Succesfully imported the Facebook Settings into Aggregator Records.', 'the-events-calendar' );
		$response->statuses = $status;

		wp_send_json( $response );
	}

	/**
	 * Method that Handles the AJAX converting of Legacy iCal Settings
	 * AJAX methods will not return anything, only print a JSON string
	 *
	 * @return void
	 */
	public function ajax_convert_ical_settings() {
		$response = (object) array(
			'status' => false,
			'text' => esc_html__( 'Error, a unknown bug happened and it was impossible to migrate the old iCal Settings to Event Aggregator, try again later.', 'the-events-calendar' ),
		);

		$post_type = get_post_type_object( Tribe__Events__Main::POSTTYPE );

		if ( empty( $post_type->cap->edit_posts ) || ! current_user_can( $post_type->cap->edit_posts ) ) {
			$response->status = false;
			$response->text = esc_html__( 'You do not have permission to migrate the iCal Facebook Settings to Event Aggregator', 'the-events-calendar' );

			wp_send_json( $response );
		}

		wp_send_json( $response );
	}

	/**
	 * Due to `empty()` been a Invalid callback for `array_filter` because it is a language
	 * constructor, we create a alias for it.
	 *
	 * @param  mixed $value Which variable will be tested
	 *
	 * @return bool
	 */
	public function is_empty( $value ) {
		return empty( $value );
	}

}
