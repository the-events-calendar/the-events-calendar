<?php
/**
 * The main controller responsible for the New Editor feature.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */

namespace TEC\Events\Classy;

use TEC\Common\Contracts\Provider\Controller as ControllerContract;
use TEC\Common\StellarWP\Assets\Asset;
use TEC\Events\Classy\Back_Compatibility\Editor;
use TEC\Events\Classy\Back_Compatibility\Editor_Utils;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;
use WP_Block_Editor_Context;
use WP_Post;
use Tribe__Date_Utils as Date_Utils;

/**
 * Class Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Classy;
 */
class Controller extends ControllerContract {

	/**
	 * The name of the constant that will be used to disable the feature.
	 * Setting it to a truthy value will disable the feature.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public const DISABLED = 'TEC_CLASSY_EDITOR_DISABLED';

	/**
	 * Detects, based on constants, environment variables whether the feature is active or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the feature is active or not.
	 */
	private static function is_feature_active(): bool {
		// The constant to disable the feature is defined and it's truthy.
		if ( defined( self::DISABLED ) && constant( self::DISABLED ) ) {
			return false;
		}

		// The environment variable to disable the feature is truthy.
		if ( getenv( self::DISABLED ) ) {
			return false;
		}

		// Read an option value to determine if the feature should be active or not.
		$active = (bool) get_option( 'tec_events_classy_editor_enabled', true );

		/**
		 * Allows filtering whether the whole Classy feature should be activated or not.
		 *
		 * Note: this filter will only apply if the disable constant or env var
		 * are not set or are set to falsy values.
		 *
		 * @since TBD
		 *
		 * @param bool $active Defaults to `true`.
		 */
		return (bool) apply_filters( 'tec_events_classy_editor_enabled', $active );
	}

	/**
	 * Returns true.
	 *
	 * The purpose of this method is to provide a uniquely identifiable method to be used in filters.
	 * This will allow removing the method hooked by this provider from filters, in place of removing
	 * a generic `__return_true` that might have been added by some other code.
	 *
	 * @since TBD
	 *
	 * @return true The boolean value `true`.
	 */
	public static function return_true(): bool {
		return true;
	}

	/**
	 * Return false.
	 *
	 * The purpose of this method is to provide a uniquely identifiable method to be used in filters.
	 * This will allow removing the method hooked by this provider from filters, in place of removing
	 * a generic `__return_false` that might have been added by some other code.
	 *
	 * @since TBD
	 *
	 * @return false The boolean value `false`.
	 */
	public static function return_false(): bool {
		return false;
	}

	/**
	 * Registers required filters early, before other plugins load.
	 *
	 * The main function of this code is to filter the template tag that lets other plugins
	 * and controllers know whether the Classy editor is being used or not.
	 * This function is used in the `Tribe__Events__Main::plugins_loaded` method.
	 *
	 * @since TBD
	 *
	 * @return void The Classy template tag is filtered accordingly.
	 * @see   \Tribe__Events__Main::plugins_loaded()
	 */
	public static function early_register(): void {
		if ( ! self::is_feature_active() ) {
			add_filter( 'tec_using_classy_editor', [ self::class, 'return_false' ] );

			return;
		}

		add_filter( 'tec_using_classy_editor', [ self::class, 'return_true' ] );
	}

	/**
	 * Determines if the feature is enabled or not.
	 *
	 * Since this class `early_register` method is already filtering the `tec_using_classy_editor` template
	 * tag, this method will call the template tag to know whether it should activate or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the feature is enabled or not.
	 */
	public function is_active(): bool {
		return tec_using_classy_editor();
	}

	/**
	 * Binds the implementations required by the feature and hooks the controller to the actions and filters required.
	 *
	 * @since TBD
	 *
	 * @return void Bindings are registered, the controller is hooked to actions and filters.
	 */
	protected function do_register(): void {
		// Register the `editor` binding replacement for back-compatibility purposes.
		$back_compatible_editor = new Editor();
		$this->container->singleton( 'editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor', $back_compatible_editor );
		$this->container->singleton( 'events.editor.compatibility', $back_compatible_editor );
		$this->container->singleton( 'editor.utils', new Editor_Utils() );

		// Tell Common, TEC, ET and so on NOT to load blocks.
		add_filter( 'tribe_editor_should_load_blocks', [ self::class, 'return_false' ] );

		// We're using TEC new editor.
		add_filter( 'tec_using_classy_editor', [ self::class, 'return_true' ] );

		add_filter( 'block_editor_settings_all', [ $this, 'filter_block_editor_settings' ], 100, 2 );

		add_action( 'init', [ $this, 'register_post_meta' ] );

		// Register the main assets entry point.
		$this->register_assets();

		// TESTING
		if ( str_starts_with( $_SERVER['REQUEST_URI'] ?? '', '/wp-admin/post-new.php' ) ) {
			add_filter( 'save_post_' . TEC::POSTTYPE, [ $this, 'test_filter_post_data' ], 0, 2 );
		}
		// END TESTING
	}

	/**
	 *
	 * Filters the data that is passed to the `wp_insert_post` function to add missing fields to an Event saved from
	 * the Classy Editor context while the feature and fields are developed.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post    The post object.
	 *
	 * @return void The post is updated with the missing fields.
	 */
	public function test_filter_post_data( $post_id, $post ): void {
		if ( $post->post_type !== TEC::POSTTYPE ) {
			return;
		}

		$occurrence_count = Occurrence::where( 'post_id', '=', $post_id )->count();

		if ( $occurrence_count > 0 ) {
			return;
		}

		$meta = [
			'_EventStartDate'    => '2025-09-14 10:00:00',
			'_EventDuration'     => '7200',
			'_EventStartDateUTC' => '2025-09-14 08:00:00',
			'_EventEndDate'      => '2025-09-14 12:00:00',
			'_EventEndDateUTC'   => '2025-09-14 10:00:00',
			'_EventTimezoneAbbr' => 'CEST',
			'_EventTimezone'     => 'Europe/Paris',
		];

		foreach ( $meta as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		$event_data = Event::data_from_post( $post_id );
		Event::upsert( [ 'post_id' ], $event_data );
		$event = Event::where( 'post_id', '=', $post_id )->first();
		/** @var Event $event */
		$event->occurrences()->save_occurrences();
	}

	/**
	 * Unhooks the controller from the actions and filters required by the feature.
	 *
	 * @since TBD
	 *
	 * @return void The hooked actions and filters are removed.
	 */
	public function unregister(): void {
		// Unregister the back-compat editor and utils.
		if ( $this->container->has( 'editor' ) && $this->container->get( 'editor' ) instanceof Editor ) {
			unset( $this->container['editor'] );
			unset( $this->container['events.editor'] );
			unset( $this->container['events.editor.compatibility'] );
		}

		if ( $this->container->has( 'editor.utils' ) && $this->container->get( 'editor.utils' ) instanceof Editor_Utils ) {
			unset( $this->container['editor.utils'] );
		}

		// Remove filters and actions.
		remove_filter( 'tribe_editor_should_load_blocks', [ self::class, 'return_false' ] );
		remove_filter( 'tec_using_classy_editor', [ self::class, 'return_true' ] );
		remove_filter( 'block_editor_settings_all', [ $this, 'filter_block_editor_settings' ], 100 );
		remove_filter( 'tec_using_classy_editor', [ self::class, 'return_true' ] );
		remove_filter( 'tribe_editor_should_load_blocks', [ self::class, 'return_false' ] );

		remove_action( 'init', [ $this, 'register_post_meta' ] );

		// TESTING
		remove_filter( 'wp_insert_post_data', [ $this, 'test_filter_post_data' ], 0 );
		// END TESTING
	}

	/**
	 * Registers the assets required for the Classy app.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function register_assets() {
		Asset::add(
			'tec-classy',
			'classy.js'
		)->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-classy' )
			->add_dependency( 'wp-tinymce' )
			->enqueue_on( 'enqueue_block_editor_assets' )
			->set_condition( fn() => $this->post_uses_new_editor( get_post_type() ) )
			->add_localize_script( 'tec.events.classy.data', [ $this, 'get_data' ] )
			->register();

		Asset::add(
			'tec-classy-style',
			'style-classy.css'
		)->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-classy' )
			->enqueue_on( 'enqueue_block_editor_assets' )
			->set_condition( fn() => $this->post_uses_new_editor( get_post_type() ) )
			->register();
	}

	/**
	 * Returns whether the given Post uses the New Editor.
	 *
	 * @since TBD
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool Whether the given Post uses the New Editor.
	 */
	public function post_uses_new_editor( string $post_type ): bool {
		$supported_post_types = $this->get_supported_post_types();

		return in_array( $post_type, $supported_post_types, true );
	}

	/**
	 * Filters the Block Editor Settings for a given Post Type to lock the template.
	 *
	 * @since TBD
	 *
	 * @param array<string,string>    $settings The Block Editor settings.
	 * @param WP_Block_Editor_Context $context
	 *
	 * @return array<string,string> The updated Block Editor settings.
	 */
	public function filter_block_editor_settings( array $settings, WP_Block_Editor_Context $context ) {
		if ( ! (
			$context->post instanceof WP_Post
			&& $this->post_uses_new_editor( $context->post->post_type )
		) ) {
			return $settings;
		}

		// Lock the template.
		$settings['templateLock'] = true;

		return $settings;
	}

	/**
	 * Returns the filtered list of Post Types that should be using the New Editor.
	 *
	 * @since TBD
	 *
	 * @return list<string> The filtered list of Post Types that should be using the New Editor.
	 */
	private function get_supported_post_types(): array {
		/**
		 * Filters the list of post types that use the new editor.
		 *
		 * @since TBD
		 *
		 * @param array<string> $supported_post_types The list of post types that use the new editor.
		 */
		$supported_post_types = apply_filters(
			'tec_events_classy_post_types',
			[ TEC::POSTTYPE ]
		);

		return (array) $supported_post_types;
	}

	/**
	 * Returns the data that is localized on the page for the Classy app.
	 *
	 * @since TBD
	 *
	 * @return array{
	 *     settings: array{
	 *         endOfDayCutoff: array{
	 *              hours: int<0,23>,
	 *              minutes: int<0,59>
	 *          }
	 *      }
	 * } The data that is localized on the page for the Classy app.
	 */
	public function get_data(): array {
		$timezone_string  = get_option( 'timezone_string' );
		$start_of_week    = get_option( 'start_of_week' );
		$multi_day_cutoff = tribe_get_option( 'multiDayCutoff', '00:00' );
		[ $multi_day_cutoff_hours, $multi_day_cutoff_minutes ] = array_replace(
			[ 0, 0 ],
			explode( ':', $multi_day_cutoff, 2 )
		);
		$date_with_year_format                                 = tribe_get_option( 'dateWithYearFormat', 'F j, Y' );
		$date_without_year_format                              = tribe_get_option( 'dateWithoutYearFormat', 'F j' );
		$month_and_year_format                                 = tribe_get_option( 'monthAndYearFormat', 'F Y' );
		$compact_date_format                                   = Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat', 1 ) );
		$data_time_separator                                   = tribe_get_option( 'dateTimeSeparator', ' @ ' );
		$time_range_separator                                  = tribe_get_option( 'timeRangeSeparator', ' - ' );
		$time_format = tribe_get_option( 'time_foratm', 'g:i a' );

		/**
		 * The time interval in minutes to use when populating the time picker options.
		 *
		 * @since TBD
		 *
		 * @param int $time_interval The time interval in minutes; defaults to 15 minutes.
		 */
		$time_interval = apply_filters( 'tec_events_time_picker_interval', 15 );

		return [
			'settings' => [
				'timezoneString'        => $timezone_string,
				'startOfWeek'           => $start_of_week,
				'endOfDayCutoff'        => [
					'hours'   => min( 23, (int) $multi_day_cutoff_hours ),
					'minutes' => min( 59, (int) $multi_day_cutoff_minutes ),
				],
				'dateWithYearFormat'    => $date_with_year_format,
				'dateWithoutYearFormat' => $date_without_year_format,
				'monthAndYearFormat'    => $month_and_year_format,
				'compactDateFormat'     => $compact_date_format,
				'dataTimeSeparator'     => $data_time_separator,
				'timeRangeSeparator'    => $time_range_separator,
				'timeFormat'            => $time_format,
				'timeInterval'          => $time_interval,
			],
		];
	}

	/**
	 * Registers the meta fields for the Classy app.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_post_meta(): void {
		foreach (
			[
				'_EventURL',
				'_EventStartDate',
				'_EventEndDate',
				'_EventAllDay',
				'_EventTimezone',
			] as $meta_key
		) {
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
}
