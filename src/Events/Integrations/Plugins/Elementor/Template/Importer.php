<?php
/**
 * Class to handle the importation of Elementor Single Event templates.
 *
 * @since 6.4.0
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
 * @since 6.4.0
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */
class Importer {

	/**
	 * Widget template.
	 *
	 * @since 6.4.0
	 *
	 * @var Template $template
	 */
	protected Template $template;

	/**
	 * Stores a flag to indicate if the importing process has been started on this request.
	 *
	 * This is used to prevent multiple imports from happening at the same time in the same request.
	 *
	 * @since 6.4.0
	 *
	 * @var bool
	 */
	protected bool $has_imported = false;

	/**
	 * The option key used to store whether the starter template has been imported.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected string $imported_key = 'tec_events_elementor_template_imported';

	/**
	 * Every imported elementor document will have a relationship with a document class.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected string $document_relationship_meta_key = 'tec_events_elementor_document';

	/**
	 * Gets a list of the documents to import.
	 *
	 * @since 6.4.0
	 *
	 * @return string[]
	 */
	protected function get_documents_to_import(): array {
		$documents = [
			Documents\Event_Single_Static::class,
		];

		if ( tribe( Elementor_Integration::class )->is_elementor_pro_active() ) {
			$documents[] = Documents\Event_Single_Dynamic::class;
		}

		return array_unique( array_filter( $documents ) );
	}

	/**
	 * Imports the starter template.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function import_starter_templates(): void {
		if ( ! is_admin() ) {
			return;
		}

		// Avoid running when WordPress is installing.
		if ( wp_installing() ) {
			return;
		}

		// Do not run on ajax requests.
		if ( wp_doing_ajax() ) {
			return;
		}

		// Do not import while doing cron.
		if ( wp_doing_cron() ) {
			return;
		}

		if ( $this->has_imported ) {
			return;
		}

		// We trigger the importing process once per request.
		$this->has_imported = true;

		$templates = $this->get_templates();

		if ( empty( $templates ) ) {
			$orphaned_documents = $this->get_documents_ids_with_relationship();
			foreach ( $orphaned_documents as $orphaned_document ) {
				wp_delete_post( $orphaned_document, true );
			}
		}

		$documents = $this->get_documents_to_import();

		foreach ( $documents as $document_class_name ) {
			$this->import_document( $document_class_name );
		}
	}

	/**
	 * Imports a given document base template.
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name Name of the event document class we're importing.
	 *
	 * @return false|int
	 */
	public function import_document( string $document_class_name ) {
		$template = $this->get_template( $document_class_name );
		if ( null !== $template && $this->is_valid_template_meta( $document_class_name, $template->ID ) ) {
			return false;
		}

		if ( $this->is_updating( $document_class_name ) ) {
			$this->clear_updating_status( $document_class_name );

			return false;
		}

		$this->mark_as_updating( $document_class_name );

		$template_to_use = 'starter';

		// If the document has a prepare_template_data method, call it to allow for custom data manipulation.
		if ( method_exists( $document_class_name, 'get_data_template_name' ) ) {
			$template_to_use = $document_class_name::get_data_template_name();
		}

		$elementor_template_json = $this->get_template_engine()->template( $template_to_use, [ 'document_class_name' => $document_class_name ], false );
		try {
			$elementor_template_data = json_decode( $elementor_template_json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			$this->clear_updating_status( $document_class_name );
			do_action(
				'tribe_log',
				Log::DEBUG,
				'Failed to decode the Elementor template JSON.',
				[ 'json_string' => $elementor_template_json ]
			);

			return false;
		}

		if ( ! is_array( $elementor_template_data ) ) {
			$this->clear_updating_status( $document_class_name );

			return false;
		}

		// If the document has a prepare_template_data method, call it to allow for custom data manipulation.
		if ( method_exists( $document_class_name, 'prepare_template_data' ) ) {
			/**
			 * @uses \TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single_Static::prepare_template_data()
			 * @uses \TEC\Events\Integrations\Plugins\Elementor\Template\Documents\Event_Single_Dynamic::prepare_template_data()
			 */
			$elementor_template_data = $document_class_name::prepare_template_data( $elementor_template_data );
		}

		// Ensure the template data is valid.
		if ( ! $this->is_valid_template_data( $document_class_name, $elementor_template_data ) ) {
			$this->clear_updating_status( $document_class_name );

			return false;
		}

		return $this->import_with_elementor( $document_class_name, $elementor_template_data );
	}

	/**
	 * Mark the starter template as currently being imported, this prevents multiple imports from happening at the same time.
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name Which document class name to mark as updating.
	 *
	 * @return bool
	 */
	protected function mark_as_updating( string $document_class_name ): bool {
		$templates                         = $this->get_templates();
		$templates[ $document_class_name ] = 'updating';

		return update_option( $this->imported_key, $templates );
	}

	/**
	 * Clear the updating status for the starter template importing operation.
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name Which document class name to clear the updating status for.
	 *
	 * @return bool
	 */
	protected function clear_updating_status( string $document_class_name ): bool {
		$templates = $this->get_templates();

		if ( isset( $templates[ $document_class_name ] ) ) {
			unset( $templates[ $document_class_name ] );
		}

		return update_option( $this->imported_key, $templates );
	}

	/**
	 * Check if the starter template is currently being imported.
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name Which document class name to check for.
	 *
	 * @return bool
	 */
	protected function is_updating( string $document_class_name ): bool {
		$templates = $this->get_templates();

		return isset( $templates[ $document_class_name ] ) && 'updating' === $templates[ $document_class_name ];
	}

	/**
	 * Check if the starter template has already been imported.
	 *
	 * @since 6.4.0
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
	 * @since 6.4.0
	 *
	 * @return array
	 */
	public function get_templates(): array {
		$templates = get_option( $this->imported_key, '__does_not_exist__' );

		if ( ! is_array( $templates ) ) {
			$templates = [];
		}

		return $templates;
	}

	/**
	 * Get the imported template if it exists.
	 *
	 * @since 6.4.0
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
	 * Validate the template data.
	 * This method will check for the following data in the template data:
	 * - title
	 * * - content
	 * * - version
	 * * - settings
	 * * - content[0].elements[0].elements
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name The document class name.
	 * @param mixed  $template_data       The template data.
	 *
	 * @return bool
	 */
	protected function is_valid_template_data( string $document_class_name, $template_data ): bool {
		if ( ! is_array( $template_data ) ) {
			return false;
		}

		if ( ! isset( $template_data['title'] ) ) {
			return false;
		}

		if ( ! isset( $template_data['content'] ) ) {
			return false;
		}

		if ( ! isset( $template_data['version'] ) ) {
			return false;
		}

		if ( ! isset( $template_data['settings'] ) ) {
			return false;
		}

		if ( empty( $template_data['content'][0]['elements'][0]['elements'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate the template meta for an existing created template.
	 * This method will check for the following data in the template data:
	 * - [0].elements[0].elements
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name The document class name.
	 * @param int    $post_id             The template id we will check.
	 *
	 * @return bool
	 */
	protected function is_valid_template_meta( string $document_class_name, int $post_id ): bool {
		$template_data = get_post_meta( $post_id, '_elementor_data', true );
		if ( empty( $template_data ) ) {
			return false;
		}

		try {
			$template_data = json_decode( $template_data, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			do_action(
				'tribe_log',
				Log::DEBUG,
				'Failed to decode the Elementor template JSON.',
				[ 'json_string' => $template_data ]
			);

			return false;
		}
		if ( ! is_array( $template_data ) ) {
			return false;
		}

		if ( empty( $template_data[0]['elements'][0]['elements'] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Mark the document as imported.
	 * This will create a new post meta entry with the document class name as the value.
	 *
	 * Uses the following key: `$this->document_relationship_meta_key`
	 *
	 * @see   $this->document_relationship_meta_key
	 *
	 * @since 6.4.0
	 *
	 * @param string   $document_class_name Name of the event document class.
	 * @param Document $document            Actual object from Elementor of the document.
	 *
	 * @return bool
	 */
	protected function mark_document_as_imported( string $document_class_name, Document $document ): bool {
		$post_id = $document->get_post()->ID;

		return update_post_meta( $post_id, $this->document_relationship_meta_key, wp_slash( $document_class_name ) );
	}


	/**
	 * Get the document class name from the post ID.
	 * This will get the document class name from the post meta of a given document.
	 *
	 * Uses the following key: `$this->document_relationship_meta_key`
	 *
	 * @see   $this->document_relationship_meta_key
	 *
	 * @since 6.4.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return string
	 */
	protected function get_document_class_name( int $post_id ): string {
		return (string) get_post_meta( $this->document_relationship_meta_key, $post_id, true );
	}

	/**
	 * Get the post ID of a document by its class name.
	 *
	 * This will get the post ID of a document by its class name.
	 *
	 * @see   $this->document_relationship_meta_key
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name The document class name.
	 *
	 * @return int|null
	 */
	protected function get_document_post_id_by_class_name( string $document_class_name ): ?int {
		$post_ids = get_posts(
			[
				'post_type'      => Source_Local::CPT,
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'   => $this->document_relationship_meta_key,
						'value' => $document_class_name,
					],
				],
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		if ( empty( $post_ids ) ) {
			return null;
		}

		return reset( $post_ids );
	}

	/**
	 * Get all documents that have a relationship with a document class.
	 *
	 * @since 6.4.0
	 *
	 * @return array
	 */
	protected function get_documents_ids_with_relationship(): array {
		$post_ids = get_posts(
			[
				'post_type'  => Source_Local::CPT,
				'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'     => $this->document_relationship_meta_key,
						'compare' => 'EXISTS',
					],
				],
				'fields'     => 'ids',
			]
		);

		if ( empty( $post_ids ) ) {
			return [];
		}

		return $post_ids;
	}

	/**
	 * Import the template using Elementor's methods.
	 *
	 * @since 6.4.0
	 *
	 * @param string $document_class_name The document class name.
	 * @param array $template_data The template data.
	 *
	 * @return false|int
	 */
	public function import_with_elementor( string $document_class_name, array $template_data ) {
		if ( ! class_exists( $document_class_name ) ) {
			$this->clear_updating_status( $document_class_name );

			return false;
		}

		if ( ! is_subclass_of( $document_class_name, Document::class ) ) {
			$this->clear_updating_status( $document_class_name );

			return false;
		}

		$existing_document_id = $this->get_document_post_id_by_class_name( $document_class_name );
		if ( null !== $existing_document_id && ! $this->is_valid_template_meta( $document_class_name, $existing_document_id ) ) {
			wp_delete_post( $existing_document_id, true );
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
		$templates                         = $this->get_templates();
		$templates[ $document_class_name ] = $document->get_post()->ID;
		$updated                           = update_option( $this->imported_key, $templates );

		$this->mark_document_as_imported( $document_class_name, $document );

		if ( ! $updated ) {
			$this->clear_updating_status( $document_class_name );

			return false;
		}

		return $document->get_post()->ID;
	}

	/**
	 * Gets the template engine for handling template importing.
	 *
	 * @since 6.4.0
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
