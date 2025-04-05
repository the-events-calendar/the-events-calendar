<?php
/**
 * Service Provider for interfacing with TEC\Common\Site Health.
 *
 * @since   6.1.0
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\Contracts\Service_Provider;

 /**
  * Class Provider
  *
  * @since   6.1.0
  * @package TEC\Events\Site_Health
  */
class Provider extends Service_Provider {

	/**
	 * Internal placeholder to pass around the section slug.
	 *
	 * @since 6.1.0
	 *
	 * @var string
	 */
	protected $slug;

	public function register() {
		$this->slug = Info_Section::get_slug();
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add the action hooks.
	 *
	 * @since 6.1.0
	 */
	public function add_actions() {

	}


	/**
	 * Add the filter hooks.
	 *
	 * @since 6.1.0
	 */
	public function add_filters() {
		add_filter( 'tec_debug_info_sections', [ $this, 'filter_include_sections' ] );
	}


	/**
	 * Includes the Section for The Events Calendar.
	 *
	 * @since 6.1.0
	 *
	 * @param array<string, \TEC\Common\Site_Health\Info_Section_Abstract> $sections Existing sections.
	 *
	 * @return array<string, \TEC\Common\Site_Health\Info_Section_Abstract>
	 */
	public function filter_include_sections( $sections ) {
		$sections[ Info_Section::get_slug() ] = $this->container->make( Info_Section::class );

		return $sections;
	}

}
