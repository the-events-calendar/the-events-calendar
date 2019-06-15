<?php
/**
 * The base template all Views will use to locate, manage and render their HTML code.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

use Tribe\Traits\Cache_User;
use Tribe__Repository__Interface as Repository_Interface;
use Tribe__Template as Base_Template;
use Tribe__Utils__Array as Arr;

/**
 * Class Template
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */
class Template extends Base_Template {
	use Cache_User;

	/**
	 * The slug the template should use to build its path.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The repository instance that provided the template with posts, if any.
	 *
	 * @var Repository_Interface
	 */
	protected $repository;

	/**
	 * An array cache to keep track of  resolved template files on a per-name basis.
	 * The file look-around needs not to be performed twice per request.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected $template_file_cache = [];

	/**
	 * Renders and returns the View template contents.
	 *
	 * @since 4.9.2
	 *
	 * @param array $context_overrides Any context data you need to expose to this file
	 *
	 * @return string The rendered template contents.
	 */
	public function render( array $context_overrides = [] ) {
		$context = wp_parse_args( $context_overrides, $this->context );
		$context['_context'] = $context;

		return parent::template( $this->slug, $context, false );
	}

	/**
	 * Template constructor.
	 *
	 * @param string $slug The slug the template should use to build its path.
	 *
	 * @since 4.9.2
	 *
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
		// Set some global defaults all Views are likely to search for; those will be overridden by each View.
		$this->set_values( [
			'slug'     => $slug,
			'prev_url' => '',
			'next_url' => '',
		], false );
		$this->set_template_origin( tribe( 'tec.main' ) )
		     ->set_template_folder( 'src/views/v2' )
		     ->set_template_folder_lookup( true )
		     ->set_template_context_extract( true );
	}

	/**
	 * Returns the template file the View will use to render.
	 *
	 * If a template cannot be found for the view then the base template for the view will be returned.
	 *
	 * @since 4.9.2
	 *
	 * @param string|array|null $name Either a specific name to check, the frgments of a name to check, or `null` to let
	 *                                the view pick the template according to the template override rules.
	 *
	 * @return string The path to the template file the View will use to render its contents.
	 */
	public function get_template_file( $name = null ) {
		$name = null !== $name ? $name : $this->slug;

		$cache_key = is_array( $name ) ? implode( '/', $name ) : $name;

		$cached = Arr::get( $this->template_file_cache, $cache_key, false );
		if ( $cached ) {
			return $cached;
		}

		$template = parent::get_template_file( $name );

		$file = false !== $template
			? $template
			: $this->get_base_template_file();

		$this->template_file_cache[ $cache_key ] = $file;

		return $file;
	}

	/**
	 * Returns the absolute path to the view base template file.
	 *
	 * @since 4.9.2
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
	 * @since 4.9.2
	 *
	 * @return string The absolute path to the Views "not found" template.
	 */
	public function get_not_found_template() {
		return parent::get_template_file( 'not-found' );
	}

	/**
	 * Sets the template slug.
	 *
	 * @since TBD
	 *
	 * @param string $slug The slug the template should use.
	 */
	public function set_slug( string $slug ) {
		$this->slug = $slug;
	}

	/**
	 * Returns the current template slug, either set in the constructor or using the `set_slug` method.
	 *
	 * @since TBD
	 *
	 * @return string The current template slug.
	 */
	public function get_slug() {
		return $this->slug;
	}
}
