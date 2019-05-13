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
		     ->set_template_folder_lookup( true );
	}
}
