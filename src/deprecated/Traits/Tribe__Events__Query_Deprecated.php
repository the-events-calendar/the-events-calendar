<?php
/**
 * Handles the `Tribe__Events__Query` class deprecated methods.
 *
 * This trait will only make sense in the context of the `Tribe__Events__Query` class
 * and it should not be used elsewhere.
 *
 * @since 6.0.6
 */

/**
 * Trait Tribe__Events__Query_Deprecated.
 *
 * @since 6.0.6
 */
trait Tribe__Events__Query_Deprecated {
	/**
	 * Is hooked by init() filter to parse the WP_Query arguments for main and alt queries.
	 *
	 * @deprecated 6.0.0 Query filtering is now handled in the Views v2 component or the Custom Tables v1 component.
	 *
	 * @param object $query WP_Query object args supplied or default
	 *
	 * @return WP_Query $query The query, unmodified.
	 */
	public static function pre_get_posts( $query ) {
		_deprecated_function(
			'Tribe__Events__Query::pre_get_posts',
			'6.0.0',
			'No replacement avaialble.'
		);
	}
}