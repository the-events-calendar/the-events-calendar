<?php
/**
 * Class to handle the importation of Elementor Single Event templates.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use Elementor\Core\Base\Document;
use TEC\Events\Integrations\Plugins\Elementor\Controller as Elementor_Integration;
use WP_Post;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Plugin;

use Tribe__Template as Template;
use Tribe__Log as Log;

/**
 * Class Importer
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */
class Importer {

	/**
	 * Widget template.
	 *
	 * @since TBD
	 *
	 * @var Template $template
	 */
	protected Template $template;

	/**
	 * The option key used to store whether the starter template has been imported.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $imported_key = 'tec_events_elementor_template_imported';

	/**
	 * Gets a list of the documents to import.
	 *
	 * @since TBD
	 *
	 * @return string[]
	 */
	protected function get_documents_to_import(): array {
		$documents = [
			Documents\Event_Single::class,
		];

		if ( tribe( Elementor_Integration::class )->is_elementor_pro_active() ) {
			$documents[] = Documents\Event_Single_Pro::class;
		}

		return $documents;
	}

	/**
	 * Imports the starter template.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function import_starter_templates(): void {
		// Avoid running when WordPress is installing.
		if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
			return;
		}

		$documents = $this->get_documents_to_import();

		foreach ( $documents as $document_class_name ) {
			$this->import_document( $document_class_name );
		}
	}

	/**
	 * Imports a given document base template.
	 *
	 * @since TBD
	 *
	 * @param string $document_class_name
	 *
	 * @return false|int
	 */
	public function import_document( string $document_class_name ) {
		if ( $this->is_template_imported( $document_class_name ) ) {
			return false;
		}

		$template_to_use = 'starter';

		// If the document has a prepare_template_data method, call it to allow for custom data manipulation.
		if ( method_exists( $document_class_name, 'get_data_template_name' ) ) {
			$template_to_use = $document_class_name::get_data_template_name();
		}

		$elementor_template_json = $this->get_template_engine()->template( $template_to_use, [ 'document_class_name' => $document_class_name ], false );
		try {
			$elementor_template_data = json_decode( $elementor_template_json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			do_action( 'tribe_log', Log::DEBUG, 'Failed to decode the Elementor template JSON.', [
				'json_string' => $elementor_template_json,
			] );
			return false;
		}

		if ( ! is_array( $elementor_template_data ) ) {
			return false;
		}

		// If the document has a prepare_template_data method, call it to allow for custom data manipulation.
		if ( method_exists( $document_class_name, 'prepare_template_data' ) ) {
			/**
			 * @uses \TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single::prepare_template_data()
			 * @uses \TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single_Pro::prepare_template_data()
			 */
			$elementor_template_data = $document_class_name::prepare_template_data( $elementor_template_data );
		}

		return $this->import_with_elementor( $document_class_name, $elementor_template_data );
	}

	/**
	 * Check if the starter template has already been imported.
	 *
	 * @since TBD
	 *
	 * @param string $document_class_name Which document class name to check for.
	 *
	 * @return bool True if imported, false otherwise.
	 */
	public function is_template_imported( string $document_class_name ): bool {
		return null !== $this->get_template( $document_class_name );
	}

	/**
	 * Get the imported template if it exists.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_templates(): array {
		$templates = get_option( $this->imported_key, [] );

		if ( ! is_array( $templates ) ) {
			$templates = [];
		}

		return $templates;
	}

	/**
	 * Get the imported template if it exists.
	 *
	 * @since TBD
	 *
	 * @param string $document_class_name Which document class name to get the template for.
	 *
	 * @return ?WP_Post
	 */
	public function get_template( string $document_class_name ): ?WP_Post {
		$templates = $this->get_templates();

		if ( ! isset( $templates[ $document_class_name ] ) ) {
			return null;
		}

		$template_id = $templates[ $document_class_name ];

		if ( ! $template_id ) {
			return null;
		}

		$template = get_post( $template_id );

		if ( ! $template instanceof WP_Post ) {
			return null;
		}

		if ( Source_Local::CPT !== $template->post_type ) {
			return null;
		}

		if ( 'publish' !== $template->post_status ) {
			return null;
		}

		return $template;
	}

	/**
	 * Import the template using Elementor's methods.
	 *
	 * @param array $template_data The template data.
	 *
	 * @return false|int
	 */
	public function import_with_elementor( string $document_class_name, array $template_data ) {
		if ( ! class_exists( $document_class_name ) ) {
			return false;
		}

		if ( ! is_subclass_of( $document_class_name, Document::class ) ) {
			return false;
		}

		$document = Plugin::$instance->documents->create(
			$document_class_name::get_type(),
			[
				'post_title'  => $template_data['title'],
				'post_type'   => Source_Local::CPT,
				'post_status' => 'publish',
			]
		);

		if ( is_wp_error( $document ) ) {
			return false;
		}

		$document->import( $template_data );
		$templates = $this->get_templates();
		$templates[ $document_class_name ] = $document->get_post()->ID;
		$updated = update_option( $this->imported_key, $templates );

		if ( ! $updated ) {
			return false;
		}

		return $document->get_post()->ID;
	}

	/**
	 * Gets the template engine for handling template importing.
	 *
	 * @since TBD
	 *
	 * @return Template
	 */
	protected function get_template_engine(): Template {
		if ( ! isset( $this->template ) ) {
			$this->template = new Template();
			$this->template->set_template_origin( tribe( 'tec.main' ) );
			$this->template->set_template_folder( 'src/views/integrations/elementor/templates' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( true );
		}

		return $this->template;
	}
}
