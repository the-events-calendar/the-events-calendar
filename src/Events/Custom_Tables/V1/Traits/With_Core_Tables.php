<?php
/**
 * Implements convenience methods to gather information about the Core WordPress tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */

namespace TEC\Events\Custom_Tables\V1\Traits;

/**
 * Trait With_Core_Tables
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Traits
 */
trait With_Core_Tables {

	/**
	 * Returns a list of the `posts` table columns.
	 *
	 * @since 6.0.0
	 *
	 * @return array<string> A list of the `posts` table columns.
	 */
	protected function get_posts_table_columns() {
		return [
			'ID',
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_content',
			'post_title',
			'post_excerpt',
			'post_status',
			'comment_status',
			'ping_status',
			'post_password',
			'post_name',
			'to_ping',
			'pinged',
			'post_modified',
			'post_modified_gmt',
			'post_content_filtered',
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			'post_mime_type',
			'comment_count',
		];
	}
}
