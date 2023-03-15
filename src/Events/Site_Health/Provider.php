<?php
/**
 * Service Provider for interfacing with TEC\Common\Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\lucatume\DI52\ServiceProvider as ServiceProvider;

 /**
  * Class Provider
  *
  * @since   TBD

  * @package TEC\Events\Site_Health
  */
class Provider extends ServiceProvider {

	/**
	 * Internal placeholder to pass around the section slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug;

	public function register() {
		$this->slug = Info_Section::get_slug();
		$this->add_actions();
		$this->add_filters();
	}

	public function add_actions() {

	}

	public function add_filters() {
		add_filter( 'tec_debug_info_sections', [ $this, 'add_section' ] );
		add_filter( "tec_debug_info_section_{$this->slug}_get_fields", [ $this, 'get_section_fields' ], 10 );
	}

	public function add_section( $sections ) {
		$sections[ Info_Section::get_slug() ] = $this->container->make( Info_Section::class );

		return $sections;
	}

	public function get_section_fields( $value ) {
		return $this->container->make( Info_Section::class )->add_fields();
	}

}
