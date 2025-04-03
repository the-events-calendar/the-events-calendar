<?php
/**
 * Elementor Templates Controller.
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use Elementor\Core\Base\Document;
use ElementorPro\Modules\ThemeBuilder\Module;
use TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single_Static;
use WP_Post;

use Elementor\Plugin;
use TEC\Events\Integrations\Plugins\Elementor\Controller as Elementor_Integration;
use Tribe\Events\Views\V2\Template_Bootstrap;
use Elementor\Core\Documents_Manager;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

use Tribe__Template as Template;
use Tribe__Events__Main as TEC;

/**
 * Class Controller
 *
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */
class Controller extends Controller_Contract {

	/**
	 * Instance of the template class.
	 *
	 * @since 6.4.0
	 *
	 * @var Template
	 */
	protected Template $template;

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
	 */
	public function add_actions(): void {
		add_action( 'elementor/documents/register', [ $this, 'action_register_elementor_documents' ] );
		add_action( 'wp', [ $this, 'action_import_starter_templates' ] );
		add_action( 'added_post_meta', [ $this, 'action_ensure_document_type' ], 15, 4 );
		add_action( 'updated_post_meta', [ $this, 'action_ensure_document_type' ], 15, 4 );
	}

	/**
	 * Removes the actions hooked by this class.
	 *
	 * @since 6.4.0
	 */
	public function remove_actions(): void {
		remove_action( 'elementor/documents/register', [ $this, 'action_register_elementor_documents' ] );
		remove_action( 'wp', [ $this, 'action_import_starter_templates' ] );
		remove_action( 'added_post_meta', [ $this, 'action_ensure_document_type' ], 15 );
		remove_action( 'updated_post_meta', [ $this, 'action_ensure_document_type' ], 15 );
	}

	/**
	 * Adds the filters required by each template component.
	 *
	 * @since 6.4.0
	 */
	public function add_filters(): void {
		add_filter( 'tribe_events_template_single-tribe_events.php', [ $this, 'filter_override_event_template' ] );
		add_filter( 'tribe_events_template_single-event.php', [ $this, 'filter_override_event_template' ] );
		add_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		add_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10, 3 );
	}

	/**
	 * Removes the filters hooked by this class.
	 *
	 * @since 6.4.0
	 */
	public function remove_filters(): void {
		remove_filter( 'tec_events_should_display_events_template_setting', '__return_false' );
		remove_filter( 'tribe_events_template_single-event.php', [ $this, 'filter_override_event_template' ] );
		remove_filter( 'tribe_get_option_tribeEventsTemplate', [ $this, 'filter_events_template_setting_option' ] );
		remove_filter( 'tribe_get_single_option', [ $this, 'filter_tribe_get_single_option' ], 10 );
	}

	/**
	 * Force the correct Single Event template object for Elementor theme.
	 *
	 * @since 6.4.0
	 *
	 * @param string $option The value of the option.
	 *
	 * @return string $option The original value, or an empty string signifying the default events template.
	 */
	public function filter_events_template_setting_option( $option ): string {
		// Only for events.
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return $option;
		}

		// Only for single events.
		if ( ! tribe( Template_Bootstrap::class )->is_single_event() ) {
			return $option;
		}

		/**
		 * Allows filtering of the events template setting option override.
		 *
		 * @since 6.4.1
		 *
		 * @param string $value  The value of the option. If this is set to anything other than an empty string,
		 *                       it will prevent the use of the provided Elementor templates.
		 * @param string $option The original value, or an empty string signifying the default events template.
		 */
		return apply_filters( 'tec_events_filter_events_template_setting_option', '', $option );
	}

	/**
	 * Override the get_single_option to return the default event template when Elementor is active.
	 *
	 * @since 5.14.2
	 *
	 * @param mixed  $option        Results (value) of option query.
	 * @param string $default_value The default value.
	 * @param string $option_name   Name of the option.
	 *
	 * @return mixed results of option query.
	 */
	public function filter_tribe_get_single_option( $option, $default_value, $option_name ) {
		// ONly this option.
		if ( 'tribeEventsTemplate' !== $option_name ) {
			return $option;
		}

		// Only for events.
		if ( ! is_singular( TEC::POSTTYPE ) ) {
			return $option;
		}

		// Only for single events.
		if ( ! tribe( Template_Bootstrap::class )->is_single_event() ) {
			return $option;
		}

		/**
		 * Allows filtering of the events single template option override.
		 *
		 * @since 6.4.1
		 *
		 * @param string $value  The value of the `tribeEventsTemplate` option. If this is set to anything other
		 *                       than an empty string, it will prevent the use of the provided Elementor templates.
		 * @param string $option The original value, or an empty string signifying the default events template.
		 */
		return apply_filters( 'tec_events_filter_tribe_get_single_option', '', $option );
	}

	/**
	 * Include the template selection helper.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function include_template_selection_helper(): void {
		$preview = tribe_get_request_var( 'elementor-preview' );

		if ( empty( $preview ) ) {
			return;
		}

		$post = tribe_get_request_var( 'post' );

		// Only include the helper if we are looking at a single event.
		if ( ! tribe_is_event( $post ) ) {
			return;
		}

		if ( Plugin::instance()->editor->is_edit_mode() ) {
			return;
		}

		$this->get_template()->template( 'template-selection-helper' );
	}

	/**
	 * Checks if the current event needs an Elementor template override.
	 * If we have the template set to our template, use the internal blank post template
	 *
	 * @since 6.4.0
	 *
	 * @param mixed $post_id The post ID to check. If null will use the current post.
	 *
	 * @return bool
	 */
	public function is_override( $post_id = null ): bool {
		/**
		 * Filters whether to bypass the template override for events.
		 *
		 * This filter allows developers to short-circuit the template override process
		 * and use the default template instead. Bypassing the override disables application of the provided templates.
		 *
		 * @since 6.5.2
		 *
		 * @param bool  $bypass  Whether to bypass the template override. Default is false.
		 * @param mixed $post_id The post ID being checked. Will be null if using the current post.
		 *
		 * @return bool Whether to bypass the template override. Default is false.
		 */
		$bypass_override = apply_filters( 'tec_events_integration_elementor_bypass_template_override', false, $post_id );

		// Allow users to bypass the override.
		if ( $bypass_override ) {
			return false;
		}

		$template = tribe( Importer::class )->get_template( Event_Single_Static::class );

		// Ensure we have a template to use.
		if ( null === $template ) {
			return false;
		}

		if ( null === $post_id ) {
			$post_id = get_the_ID();
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( $post->post_type !== TEC::POSTTYPE ) {
			return false;
		}

		$document = Plugin::$instance->documents->get( $post->ID );

		if ( ! $document ) {
			return false;
		}

		if ( ! tribe( Elementor_Integration::class )->is_elementor_pro_active() ) {
			return $document->is_built_with_elementor();
		}

		$documents_by_conditions = Module::instance()->get_conditions_manager()->get_documents_for_location( 'single' );

		return ! ( empty( $documents_by_conditions ) && ! $document->is_built_with_elementor() );
	}

	/**
	 * Registers the Elementor documents.
	 * A document in Elementor's context represents the basic type of post (e.g., page, section, widget).
	 *
	 * @since 6.4.0
	 *
	 * @param Documents_Manager $documents_manager The documents' manager.
	 */
	public function action_register_elementor_documents( Documents_Manager $documents_manager ): void {
		if ( ! class_exists( 'Elementor\Modules\Library\Documents\Page' ) ) {
			return;
		}

		if ( tribe( Elementor_Integration::class )->is_elementor_pro_active() ) {

			$documents_manager->register_document_type(
				Documents\Event_Single_Dynamic::get_type(),
				Documents\Event_Single_Dynamic::class
			);
		}

		$documents_manager->register_document_type(
			Documents\Event_Single_Static::get_type(),
			Documents\Event_Single_Static::class
		);
	}

	/**
	 * Ensures that the document type is set correctly when the Document Type meta is updated.
	 *
	 * @since 6.4.0
	 *
	 * @param int    $mid        The meta ID after successful update.
	 * @param int    $object_id  ID of the object metadata is for.
	 * @param string $meta_key   Metadata key.
	 * @param mixed  $meta_value Metadata value.
	 *
	 * @return void
	 */
	public function action_ensure_document_type( $mid, $object_id, $meta_key, $meta_value ): void {
		if ( Document::TYPE_META_KEY !== $meta_key ) {
			return;
		}

		if ( ! tribe_is_event( $object_id ) ) {
			return;
		}

		remove_action( 'updated_post_meta', [ $this, 'action_ensure_document_type' ], 15 );

		update_metadata_by_mid( 'post', $mid, Documents\Event_Single_Static::get_type() );

		add_action( 'updated_post_meta', [ $this, 'action_ensure_document_type' ], 15, 4 );
	}

	/**
	 * Overrides the single event template based on the selected Elementor Template.
	 * Priority is given to individual event templates over the global setting for all events.
	 *
	 * @since 6.4.0
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

		if ( ! $this->is_override() ) {
			return $file;
		}

		// Potentially inject the template selection helper.
		add_action( 'tribe_events_before_view', [ $this, 'include_template_selection_helper' ] );

		return $this->get_blank_file();
	}

	/**
	 * Retrieves the path to the blank file.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function action_import_starter_templates(): void {
		$this->container->make( Importer::class )->import_starter_templates();
	}

	/**
	 * Gets the template instance used to setup the rendering html.
	 *
	 * @since 1.0.0
	 *
	 * @return Template
	 */
	public function get_template() {
		if ( empty( $this->template ) ) {
			$this->template = new Template();
			$this->template->set_template_origin( tribe( 'tec.main' ) );
			$this->template->set_template_folder( 'src/admin-views/integrations/plugins/elementor' );
			$this->template->set_template_context_extract( true );
		}

		return $this->template;
	}
}
