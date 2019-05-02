<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	const NAME_SPACE = 'tribe/views/v2';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		require_once tribe( 'tec.main' )->plugin_path . 'src/functions/views/provider.php';

		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		$this->container->singleton( Template_Bootstrap::class, Template_Bootstrap::class );
		$this->container->singleton( Template\Event::class, Template\Event::class );
		$this->container->singleton( Template\Page::class, Template\Page::class );
		$this->container->singleton( Kitchen_Sink::class, Kitchen_Sink::class );

		/**
		 * To remove a filter:
		 * remove_filter( 'some_filter', [ tribe( Views\V2\Filters::class ), 'some_filtering_method' ] );
		 * remove_filter( 'some_filter', [ tribe( 'views-v2.filters' ), 'some_filtering_method' ] );
		 */
		$filters = new Filters( $this->container );
		$filters->register();
		$this->container->singleton( Filters::class, $filters );
		$this->container->singleton( 'views-v2.filters', $filters );

		View::set_container( $this->container );
	}

}