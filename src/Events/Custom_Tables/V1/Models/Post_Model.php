<?php
/**
 * The API implemented by Models based, and persisted by means of, WordPress posts only, without the use of
 * custom tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

/**
 * Interface Post_Model
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */
interface Post_Model {
	/**
	 * Validates, or inserts, posts of the post model type.
	 *
	 * @since 6.0.0
	 *
	 * @param array<int,string>|int|string A post ID to validate, the title of a post to insert or a list of those.
	 * @param array<string,mixed> $create_overrides A map of overrides that should be used to insert the post if
	 *                                              not present.
	 *
	 * @return array<int>|int Either the validated or inserted post ID, or a list of them. The method will return
	 *                        `0` to indicate the post could is not valid or could not be created.
	 */
	public static function vinsert( $posts, array $create_overrides = [] );
}
