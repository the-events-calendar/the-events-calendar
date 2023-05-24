<?php
/**
 * Service Provider for interfacing with TEC\Common\Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Site_Health
 */

namespace TEC\Events\Site_Health;

use TEC\Common\Contracts\Service_Provider;

 /**
  * Class Provider
  *
  * @since   TBD

  * @package TEC\Events\Site_Health
  */
class Provider extends Service_Provider {

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

	/**
	 * Add the action hooks.
	 *
	 * @since TBD
	 */
	public function add_actions() {

	}

	public function add_filters() {
		add_filter( 'tec_debug_info_sections', [ $this, 'filter_include_sections' ] );
	}

	public function filter_include_sections( $sections ) {
		$sections[ Info_Section::get_slug() ] = $this->container->make( Info_Section::class );

		return $sections;
	}

}
