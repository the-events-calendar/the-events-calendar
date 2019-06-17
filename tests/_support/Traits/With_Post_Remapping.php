<?php
/**
 * Provides methods to remap posts to other posts in cache.
 *
 * @package Tribe\Events\Test\Traits
 */

namespace Tribe\Events\Test\Traits;

/**
 * Class With_Post_Remapping
 * @package Tribe\Events\Test\Traits
 */
trait With_Post_Remapping {

	/**
	 * Remaps, pre-filling the cache, some posts to some fake targets.
	 *
	 * @param array $posts   The posts to remap in the cache.
	 * @param array $targets The targets to remap the posts to.
	 */
	protected function remap_posts( array $posts, array $targets ) {
		$this->check_remap_counts( $posts, $targets );
		$this->check_remap_posts( $posts );
		$this->check_remap_targets( $targets );

		$iterator = new \MultipleIterator();
		$iterator->attachIterator( new \ArrayIterator( $posts ) );
		$iterator->attachIterator( new \ArrayIterator( $targets ) );
		foreach ( $iterator as list( $post, $target ) ) {
			$post_id      = $post instanceof \WP_Post
				? $post->ID
				: (int) $post;
			$remap_target = $this->get_remap_target( $target );

			$meta_input = $tax_input = false;

			if ( isset( $remap_target['meta_input'] ) ) {
				$meta_input = $remap_target['meta_input'];
				unset( $remap_target['meta_input'] );
			}

			$remap_id = $remap_target['ID'];

			// The same data will be returned fetching the original or remapped post.
			wp_cache_set( $post_id, (object) $remap_target, 'posts' );
			wp_cache_set( $remap_id, (object) $remap_target, 'posts' );

			if ( ! empty( $meta_input ) ) {
				wp_cache_set( $post_id, $meta_input, 'post_meta' );
				wp_cache_set( $remap_id, $meta_input, 'post_meta' );
			}

			// @todo support tax_input.
		}
	}

	/**
	 * Checks the amount of remap posts and targets is correct.
	 *
	 * @param array $actual  The posts to remap.
	 * @param array $targets The targets to remap the posts to.
	 */
	private function check_remap_counts( array $actual, array $targets ) {
		$actual_count = count( $actual );
		$target_count = count( $targets );
		if ( $actual_count !== $target_count ) {
			throw new \InvalidArgumentException(
				"The number of actual and target posts must be the same; it's {$actual_count} and {$target_count}."
			);
		}
	}

	/**
	 * Checks all the posts to remap are either post objects or post IDs.
	 *
	 * @param array $posts The array of posts to check.
	 */
	private function check_remap_posts( array $posts ) {
		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post || is_numeric( $post ) ) {
				throw new \InvalidArgumentException(
					'Each post to remap should be WP_Post instance or a post ID; one is not: ' .
					json_encode( $post, JSON_PRETTY_PRINT )
				);
			}
		}
	}

	/**
	 * Checks each target is valid.
	 *
	 * @param array $targets An array of targets to check.
	 */
	private function check_remap_targets( array $targets ) {
		$remapped_ids = [];

		foreach ( $targets as $target ) {
			if ( ! is_string( $target ) ) {
				throw new \InvalidArgumentException(
					'Target ' . json_encode( $target ) . ' is not a string.'
				);
			}

			$file = $this->get_remap_target_file( $target );

			if ( ! file_exists( $file ) ) {
				throw new \InvalidArgumentException(
					"Target {$target} file could not be found; does [$file] file exist?"
				);
			}

			$remap_target = $this->get_remap_target( $target );

			if ( ! isset( $remap_target['ID'] ) ) {
				throw new \InvalidArgumentException(
					"Target {$target} file could be found but it does not have an ID property set."
				);
			}

			$match = array_search( $remap_target['ID'], $remapped_ids, true );

			if ( $match ) {
				throw new \InvalidArgumentException(
					"Target {$target} file could be found but the ID property is already mapped to {$match}."
				);
			}

			$remapped_ids[ $target ] = (int) $remap_target['ID'];
		}
	}

	/**
	 * Returns the absolute path to a remap JSON file.
	 *
	 * @param string $target The target, in the format `path/to/file.identifier.subidentifier`.
	 *
	 * @return string The absolute path to the target file.
	 */
	private function get_remap_target_file( $target ) {
		$append_path = 'remap/' . preg_replace( '~\\.json$~', '', $target );

		return codecept_data_dir( $append_path . '.json' );
	}

	/**
	 * Returns the array array for a target identifier.
	 *
	 * @param string $target The target to return the array for.
	 *
	 * @return array|false Either the target array or `false` if the identifier could not be found.
	 */
	private function get_remap_target( $target ) {
		$file     = $this->get_remap_target_file( $target );
		$contents = file_get_contents( $file );
		$decoded  = json_decode( $contents, true );

		return (array)$decoded;
	}
}
