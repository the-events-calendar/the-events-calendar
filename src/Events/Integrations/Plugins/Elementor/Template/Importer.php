<?php
/**
 * Class to handle the importation of Elementor Single Event templates.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Elementor\Template
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Template;

use WP_Post;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Plugin;

use Tribe__Template as Template;

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
	 * Imports the starter template.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function import_starter_template(): void {
		// Avoid running when WordPress is installing.
		if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
			return;
		}

		if ( $this->is_template_imported() ) {
			return;
		}

		$elementor_template_json = $this->get_template_engine()->template( 'starter', [], false );
		$elementor_template_data = json_decode( $elementor_template_json, true );
		$this->import_with_elementor( $elementor_template_data );
	}

	/**
	 * Check if the starter template has already been imported.
	 *
	 * @since TBD
	 *
	 * @return bool True if imported, false otherwise.
	 */
	public function is_template_imported(): bool {
		return null !== $this->get_template();
	}

	/**
	 * Get the imported template if it exists.
	 *
	 * @since TBD
	 *
	 * @return ?WP_Post
	 */
	public function get_template(): ?WP_Post {
		$template_id = get_option( $this->imported_key, null );

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
	 * @return bool
	 */
	public function import_with_elementor( array $template_data ): bool {
		$document = Plugin::$instance->documents->create(
			Documents\Event_Single::get_type(),
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

		return update_option( $this->imported_key, $document->get_post()->ID );
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
