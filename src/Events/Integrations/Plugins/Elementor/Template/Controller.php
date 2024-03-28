<?php
/**
 * Elementor Templates Controller.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use TEC\Events\Integrations\Plugins\Elementor\Controller as Elementor_Integration;
use Tribe\Events\Views\V2\Template_Bootstrap;
use Elementor\Core\Documents_Manager;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */
class Controller extends Controller_Contract {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->singleton( self::class, self::class );

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Unregister the hooks for the plugin.
	 */
	public function unregister(): void {
		$this->remove_actions();
		$this->remove_filters();
	}

	/**
	 * Adds the actions required by each template component.
	 *
	 * @since TBD
	 */
	public function add_actions(): void {
		add_action( 'elementor/documents/register', [ $this, 'action_register_elementor_documents' ] );
		add_action( 'init', [ $this, 'action_import_starter_template' ] );
	}

	/**
	 * Removes the actions hooked by this class.
	 *
	 * @since TBD
	 */
	public function remove_actions(): void {
		remove_action( 'elementor/documents/register', [ $this, 'action_register_elementor_documents' ] );
		remove_action( 'init', [ $this, 'action_import_starter_template' ] );
	}

	/**
	 * Adds the filters required by each template component.
	 *
	 * @since TBD
	 */
	public function add_filters(): void {
		add_filter( 'tribe_events_template_single-event.php', [ $this, 'filter_override_event_template' ] );
		add_filter( 'tec_events_should_display_events_template_setting', '__return_false' );

		add_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		add_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10, 3 );
		add_filter( 'tribe_settings_save_option_array', [ $this, 'filter_tribe_save_template_option' ], 10, 2 );


		add_filter( 'get_post_metadata', [ $this, 'bind_meta_courier' ], 25, 3 );
		add_filter( 'elementor/document/config', [ $this, 'bind_content_handler' ], 25, 2 );
	}

	/**
	 * Removes the filters hooked by this class.
	 *
	 * @since TBD
	 */
	public function remove_filters(): void {
		remove_filter( 'tribe_events_template_single-event.php', [ $this, 'filter_override_event_template' ] );
		remove_filter( 'tec_events_should_display_events_template_setting', '__return_false' );

		remove_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		remove_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10 );
		remove_filter( 'tribe_settings_save_option_array', [ $this, 'filter_tribe_save_template_option' ], 10 );

		remove_filter( 'get_post_metadata', [ $this, 'bind_meta_courier' ], 25 );
		remove_filter( 'elementor/document/config', [ $this, 'bind_content_handler' ], 25 );
	}


	/**
	 * Force the correct template object for Elementor theme.
	 *
	 * @since TBD
	 *
	 * @param string $value The value of the option.
	 *
	 * @return string $value The original value, or an empty string if FSE is active.
	 */
	public function filter_events_template_setting_option( $value ): string {
		return '';
	}

	/**
	 * Override the get_single_option to return the default event template when Elementor is active.
	 *
	 * @since 5.14.2
	 *
	 * @param mixed  $option      Results of option query.
	 * @param string $default     The default value.
	 * @param string $option_name Name of the option.
	 *
	 * @return mixed results of option query.
	 */
	public function filter_tribe_get_single_option( $option, $default, $option_name ) {
		if ( 'tribeEventsTemplate' !== $option_name ) {
			return $option;
		}

		return '';
	}

	/**
	 * Overwrite the template option on save if Elementor is active.
	 * We only support the default events template for now.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $options   The array of values to save. In the format option key => value.
	 * @param string               $option_id The main option ID.
	 *
	 * @return array<string, mixed> $options   The array of values to save. In the format option key => value.
	 */
	public function filter_tribe_save_template_option( $options, $option_id ): array {
		if ( ! is_array( $options ) ) {
			$options = [];
		}

		$options['tribeEventsTemplate'] = '';

		return $options;
	}

	/**
	 * Handle the content of the Event for better compatibility.
	 *
	 * @param array $config The additional document configuration.
	 * @param int   $id     The post ID of the document.
	 */
	public function bind_content_handler( $config, $id ): array {
		tribe( Content::class )->save_post_content_as_filtered_content( $id );

		return (array) $config;
	}

	/**
	 * Binds the Meta Courier to copy data our Document type to the Post in question.
	 *
	 * @since TBD
	 *
	 * @param mixed  $value  The value to return, either a single metadata value or an array
	 *                       of values depending on the value of `$single`. Default null.
	 * @param int    $id     ID of the object metadata is for.
	 * @param string $key    Metadata key.
	 *
	 * @return mixed
	 */
	public function bind_meta_courier( $value, $id, $key ) {
		// Prevents the clone of the Base template from happening all the time.
		if ( ! empty( $value ) ) {
			return $value;
		}

		if ( ! is_string( $key ) ) {
			return $key;
		}

		$courier = Meta_Courier::to_post( $id );
		if ( is_wp_error( $courier ) ) {
			return $value;
		}

		remove_filter( 'get_post_metadata', [ $this, 'bind_meta_courier' ], 25 );
		$carried = $courier->carry( $key );
		add_filter( 'get_post_metadata', [ $this, 'bind_meta_courier' ], 25, 3 );

		if ( is_wp_error( $carried ) ) {
			return $value;
		}

		// We already had something stored.
		if ( false === $carried ) {
			return $value;
		}

		// Avoids the filter we already hooked bv using the MID.
		$meta = get_metadata_by_mid( 'post', $carried );

		// Cannot find the metadata.
		if ( ! $meta ) {
			return $value;
		}

		return $meta->meta_value;
	}

	/**
	 * Registers the Elementor documents.
	 * A document in Elementor's context represents the basic type of post (e.g., page, section, widget).
	 *
	 * @since TBD
	 *
	 * @param Documents_Manager $documents_manager The documents' manager.
	 */
	public function action_register_elementor_documents( Documents_Manager $documents_manager ): void {
		if ( ! class_exists( 'Elementor\Modules\Library\Documents\Page' ) ) {
			return;
		}

		$class = Documents\Event_Single::class;

		if ( tribe( Elementor_Integration::class )->is_elementor_pro_active() ) {
			$class = Documents\Event_Single_Pro::class;
		}

		$documents_manager->register_document_type(
			$class::get_type(),
			$class
		);
	}

	/**
	 * Overrides the single event template based on the selected Elementor Template.
	 * Priority is given to individual event templates over the global setting for all events.
	 *
	 * @since TBD
	 *
	 * @param string $file Path to the template file.
	 *
	 * @return string Path to the template file.
	 */
	public function filter_override_event_template( $file ): string {
		// Return the original template file if not a single event.
		if ( ! tribe( Template_Bootstrap::class )->is_single_event() ) {
			return $file;
		}

		if ( ! tribe( Content::class )->is_override() ) {
			return $file;
		}

		return $this->get_blank_file();
	}

	/**
	 * Retrieves the path to the blank file.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_blank_file(): string {
		$plugin_path = trailingslashit( tribe( 'tec.main' )->pluginPath );

		return "{$plugin_path}src/views/integrations/elementor/templates/blank.php";
	}

	/**
	 * Imports the single event starter template.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function action_import_starter_template(): void {
		$this->container->make( Importer::class )->import_starter_template();
	}
}
