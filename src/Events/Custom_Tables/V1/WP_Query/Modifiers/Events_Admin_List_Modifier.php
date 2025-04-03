<?php
/**
 * Filters the admin events list view.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use Tribe__Events__Main as TEC;
use WP_Query;

/**
 * Class Events_Admin_List_Modifier
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query\Modifiers
 */
class Events_Admin_List_Modifier extends Base_Modifier {

	/**
	 * {@inheritDoc}
	 */
	public function applies_to( WP_Query $query = null ) {
		return is_admin() && $query->is_main_query() && $query->get( 'post_type' ) === TEC::POSTTYPE;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function hook() {
		add_filter( 'posts_clauses_request', [ $this, 'filter_legacy_child_events' ], 100, 2 );
	}

	/**
	 * @since 6.0.0
	 *
	 * @param array<string,string> $pieces Query clauses.
	 *
	 * @return array<string,string> The modified WHERE query clauses.
	 */
	public function filter_legacy_child_events(  $pieces ) {
		if ( ! is_array( $pieces ) ) {
			return $pieces;
		}

		$pieces['where'] .= ' AND post_parent = 0 ';
		$this->unhook();

		return $pieces;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 6.0.0
	 */
	public function unhook() {
		remove_filter( 'posts_clauses_request', [ $this, 'filter_legacy_child_events' ], 100, 2 );
	}
}
