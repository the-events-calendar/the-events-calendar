<?php
/**
 * The base template all Views will use to locate, manage and render their HTML code.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

use Tribe__Template as Base_Template;

/**
 * Class Template
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */
class Template extends Base_Template {

	/**
	 * Renders and returns the View template contents.
	 *
	 * @since TBD
	 *
	 * @param array $context_overrides Any context data you need to expose to this file
	 *
	 * @return string The rendered template contents.
	 */
	public function render( array $context_overrides = [] ) {
		$this->set(
			'relative_path',
			str_replace( WP_CONTENT_DIR, '', $this->get_base_template_file() )
		);
		$context = wp_parse_args( $context_overrides, $this->context );
		$context['_context'] = $context;

		return parent::template( $this->slug, $context, false );
	}

	/**
	 * The slug the template should use to build its path.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Template constructor.
	 *
	 * @param string $slug The slug the template should use to build its path.
	 *
	 * @since TBD
	 *
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
		$this->set( 'slug', $slug );
		$this->set_template_origin( tribe( 'tec.main' ) )
		     ->set_template_folder( 'src/views/v2' )
		     ->set_template_context_extract( true )
		     ->set_template_folder_lookup( true );
	}

	/**
	 * Returns the template file the View will use to render.
	 *
	 * If a template cannot be found for the view then the base template for the view will be returned.
	 *
	 * @param string|null $name Either a specific name to check or `null` to let the view pick the
	 *                          template according to the template override rules.
	 *
	 * @return string The path to the template file the View will use to render its contents.
	 * @since TBD
	 *
	 */
	public function get_template_file( $name = null ) {
		$name = null !== $name ? $name : $this->slug;

		$template = parent::get_template_file( $name );

		return false !== $template
			? $template
			: $this->get_base_template_file();
	}

	/**
	 * Returns the absolute path to the view base template file.
	 *
	 * @since TBD
	 *
	 * @return string The absolute path to the Views base template.
	 */
	public function get_base_template_file() {
		// Print the lookup folders as relative paths.
		$this->set( 'lookup_folders', array_map( function ( array $folder ) {
			$folder['path'] = str_replace( WP_CONTENT_DIR, '', $folder['path'] );

			return $folder;
		}, $this->get_template_path_list() ) );

		return parent::get_template_file( 'base' );
	}

	/**
	 * Returns the absolute path to the view "not found" template file.
	 *
	 * @since TBD
	 *
	 * @return string The absolute path to the Views "not found" template.
	 */
	public function get_not_found_template() {
		return parent::get_template_file( 'not-found' );
	}
}
