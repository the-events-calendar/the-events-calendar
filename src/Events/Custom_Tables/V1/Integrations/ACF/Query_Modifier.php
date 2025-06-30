<?php
/**
 * An extension of the Events-only modifier to redirect Event queries to the custom tables
 * while rendering ACF fields.
 *
 * @since 6.0.11
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations\ACF;
 */

namespace TEC\Events\Custom_Tables\V1\Integrations\ACF;

use TEC\Events\Custom_Tables\V1\WP_Query\Custom_Tables_Query;
use TEC\Events\Custom_Tables\V1\WP_Query\Modifiers\Events_Only_Modifier;
use WP_Query;
use Tribe__Events__Main as TEC;

/**
 * Class Query_Modifier.
 *
 * @since 6.0.11
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations\ACF;
 */
class Query_Modifier extends Events_Only_Modifier {
	/**
	 * Whether this query modifier should handle the query or not.
	 *
	 * @since 6.0.11
	 *
	 * @var bool
	 */
	private bool $handle = false;

	/**
	 * Whether this query modifier should handle the query or not.
	 *
	 * @since 6.0.11
	 *
	 * @param WP_Query|null $query The query object that will be modified.
	 *
	 * @return bool Whether this query modifier should handle the query or not.
	 */
	public function applies_to( WP_Query $query = null ) {
		return $this->handle
		       && $query !== null
		       && ! $query instanceof Custom_Tables_Query
		       && $this->is_query_for_post_type( $query, TEC::POSTTYPE );
	}

	/**
	 * Sets whether the query modifier should handle the query or not.
	 *
	 * @since 6.0.11
	 *
	 * @param bool $handling Whether the query modifier should handle the query or not.
	 *
	 * @return $this This query modifier instance.
	 */
	public function set_handling( bool $handling ): self {
		$this->handle = $handling;

		return $this;
	}
}
