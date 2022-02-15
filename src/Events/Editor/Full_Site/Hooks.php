<?php

namespace TEC\Events\Editor\Full_Site;

/**
 * Class Hooks
 *
 * @since   TBD
 *
 * @package TEC\Editor\Full_Site
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->add_filters();
		$this->add_actions();
	}

	protected function add_filters(): void {
		add_filter( 'get_block_templates', [ $this, 'filter_include_templates' ], 25, 3 );
	}

	protected function add_actions(): void {

	}

	public function filter_include_templates( $query_result, $query, $template_type ) {
		return $this->container->make( Templates::class )->add_events_archive( $query_result, $query, $template_type );
	}
}