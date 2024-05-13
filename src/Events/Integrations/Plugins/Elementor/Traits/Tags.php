<?php
/**
 * Provides methods for fetching tags for use in Elementor.
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Integrations\Elementor\Traits
 */

namespace TEC\Events\Integrations\Plugins\Elementor\Traits;

use Tribe__Cache as Cache;

/**
 * Trait Categories
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Integrations\Elementor\Controls\Traits
 */
trait Tags {
	/**
	 * Adds an event tag control.
	 *
	 * @since 5.4.0
	 *
	 * @return array
	 */
	protected function get_event_tags() {
		/** @var Cache $cache */
		$cache        = tribe( 'cache' );
		$cache_key    = 'tec_elementor_tags';
		$term_objects = $cache->get( $cache_key, 'save_post' );

		if ( false === $term_objects ) {
			$term_objects = get_terms(
				[
					'taxonomy' => 'post_tag',
				]
			);

			if ( is_array( $term_objects ) ) {
				$cache->set( $cache_key, $term_objects, Cache::NON_PERSISTENT, 'save_post' );
			}
		}

		$terms = [];
		foreach ( $term_objects as $term ) {
			$terms[ $term->term_id ] = $term->name;
		}

		return $terms;
	}
}
