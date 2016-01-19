<?php

class Tribe__Events__Pro__Recurrence__Queries {

	/**
	 * Collapses subsequent recurrence records when appropriate (ie, for multi-post type queries where
	 * the "Recurring event instances"/show-only-the-first-upcoming-recurring-event setting is enabled).
	 *
	 * In those situations where we do need to intervene and collapse recurring events, we re-jigger
	 * the SQL statement so the GROUP BY collapses records in the expected manner.
	 *
	 * @param string   $sql   The current SQL statement
	 * @param WP_Query $query WP Query object
	 *
	 * @return string The new SQL statement
	 */
	public static function collapse_sql( $sql, $query ) {
		global $wpdb;

		// For month, week and day views we don't want to apply this logic - unless the current query
		// belongs to a widget and just happens to be running inside one of those views
		if ( ! isset( $query->query_vars['is_tribe_widget'] ) || ! $query->query_vars['is_tribe_widget'] ) {
			if ( tribe_is_month() || tribe_is_week() || tribe_is_day() ) {
				return $sql;
			}
		}

		// If this is not an event query/a multi post type query there is no need to interfere
		if ( empty( $query->tribe_is_event ) && empty( $query->tribe_is_multi_posttype ) ) {
			return $sql;
		}

		// If the hide-recurring-events setting is not set/is false we do not need to interfere
		if ( ! isset( $query->query_vars['tribeHideRecurrence'] ) || ! $query->query_vars['tribeHideRecurrence'] ) {
			return $sql;
		}

		// If looking just for fields then let's replace the .ID with *
		if ( $query->query_vars['fields'] == 'ids' ) {
			$sql = preg_replace( "/(^SELECT\\s+DISTINCT\\s{$wpdb->posts}.)(ID)/",
				"$1*, {$wpdb->postmeta}.meta_value as 'EventStartDate'",
				$sql );
		}

		if ( $query->query_vars['fields'] == 'id=>parent' ) {
			$sql = preg_replace( "/(^SELECT\\s+DISTINCT\\s{$wpdb->posts}.ID,\\s{$wpdb->posts}.post_parent)/",
				"$1, {$wpdb->postmeta}.meta_value as 'EventStartDate'",
				$sql );
		}

		// We need to relocate the SQL_CALC_FOUND_ROWS to the outer query
		$sql = preg_replace( '/SQL_CALC_FOUND_ROWS/', '', $sql );

		// We don't want to grab the min EventStartDate or EventEndDate because without a group by that collapses everything
		$sql = preg_replace( '/MIN\((' . $wpdb->postmeta . '|tribe_event_end_date).meta_value\) as Event(Start|End)Date/',
			'$1.meta_value as Event$2Date',
			$sql );

		// Let's get rid of the group by (non-greedily stop before the ORDER BY or LIMIT)
		$sql = preg_replace( '/GROUP BY .+?(ORDER|LIMIT)/', '$1', $sql );

		// Once this becomes an inner query we need to avoid duplicating the post_date column (which will
		// otherwise be returned once from wp_posts.* and once as an alias)
		$sql = str_replace( 'AS post_date', 'AS EventStartDate', $sql );

		// The outer query should order things by EventStartDate in the same direction the inner query does by post date:
		preg_match( '/[\s,](?:EventStartDate|post_date)\s+(DESC|ASC)/', $sql, $direction );
		$direction = ( isset( $direction[1] ) && 'DESC' === $direction[1] ) ? 'DESC' : 'ASC';

		// Let's extract the LIMIT. We're going to relocate it to the outer query
		$limit_regex = '/LIMIT\s+[0-9]+(\s*,\s*[0-9]+)?/';
		preg_match( $limit_regex, $sql, $limit );

		if ( $limit ) {
			$sql   = preg_replace( $limit_regex, '', $sql );
			$limit = $limit[0];
		} else {
			$limit = '';
		}

		$group_clause = $query->query_vars['fields'] == 'id=>parent' ? 'GROUP BY ID' : 'GROUP BY IF( post_parent = 0, ID, post_parent )';

		return '
			SELECT
				SQL_CALC_FOUND_ROWS *
			FROM (
				' . $sql . "
			) a
			$group_clause
			ORDER BY EventStartDate $direction
			{$limit}
		";
	}
}