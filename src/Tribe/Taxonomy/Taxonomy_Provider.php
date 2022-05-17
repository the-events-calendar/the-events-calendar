<?php
/**
 * The Event Taxonomy Service Provider.
 *
 * @since   TBD
 * @package Tribe\Events\Taxonomy
 */

namespace Tribe\Events\Taxonomy;

use WP_Term;

/**
 * Class Taxonomy_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Taxonomy
 */
class Taxonomy_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		// Register the SP on the container
		$this->container->singleton( 'events.taxonomy.provider', $this );

		$this->add_filters();
	}

	/**
	 * Adds the filters required for taxonomies.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		add_filter( 'post_tag_row_actions', [ $this, 'event_tag_actions' ], 10, 2 );
	}

	/**
	 * Filters the post tag action links displayed for each term in the terms list table.
	 *
	 * @since TBD
	 *
	 * @param array<string|string> $actions An array of action links to be displayed.
	 * @param WP_Term              $tag     Term object.
	 *
	 * @return array<string|string> An array of action links to be displayed
	 */
	public function event_tag_actions( $actions, WP_Term $tag ) {
		return $this->container->make( Event_Tag::class )->event_tag_actions( $actions, $tag );
	}
}
