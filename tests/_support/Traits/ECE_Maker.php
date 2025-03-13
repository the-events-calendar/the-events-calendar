<?php
/**
 * Provides methods to create External Calendar Embeds
 * objects for tests.
 *
 * @package Tribe\Events\Test\Traits;
 */

namespace Tribe\Events\Test\Traits;

use TEC\Events\Calendar_Embeds\Calendar_Embeds;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

/**
 * Trait ECE_Maker.
 *
 * @package Tribe\Events\Test\Traits;
 */
trait ECE_Maker {

	use With_Uopz;

	/**
	 * The counter used to generate External Calendar Embeds objects slugs.
	 *
	 * @var int
	 */
	protected static int $counter = 0;

	/**
	 * Creates an External Calendar Embeds object.
	 *
	 * @param array $args The arguments to create the External Calendar Embeds object.
	 *
	 * @return int The External Calendar Embeds object id.
	 */
	protected function create_ece( $args = [] ): int {
		$defaults = [
			'post_type' => Calendar_Embeds::POSTTYPE,
		];

		$args = wp_parse_args( $args, $defaults );

		$counter = &self::$counter;
		$this->set_fn_return( 'wp_generate_password', function () use ( &$counter ) {
			return 'ece-static-slug-' . ( ++$counter );
		}, true );

		return $this->factory->post->create( $args );
	}

	/**
	 * Resets the counter used to generate External Calendar Embeds objects slugs.
	 *
	 * @after
	 */
	public function reset_counter() {
		self::$counter = 0;
	}

	/**
	 * Creates many External Calendar Embeds objects.
	 *
	 * @param int   $count The number of External Calendar Embeds objects to create.
	 * @param array $args  The arguments to create the External Calendar Embeds objects.
	 *
	 * @return int[] The External Calendar Embeds objects ids.
	 */
	protected function create_many_ece( $count, $args = [] ): array {
		$ids = [];
		for ( $n = 0; $n < $count; $n ++ ) {
			$ids[] = $this->create_ece( $args );
		}

		return $ids;
	}

	/**
	 * Adds tags to an External Calendar Embeds object.
	 *
	 * @param int   $ece_id The External Calendar Embeds object id.
	 * @param array $tags   The tags to add to the External Calendar Embeds object.
	 */
	protected function add_tags_to_ece( $ece_id, $tags = [] ): void {
		$tags = array_map( function( $tag ) {
			return (string) $tag;
		}, $tags );

		$result = wp_set_post_tags( $ece_id, $tags, true );

		$this->assertTrue( $result && ! is_wp_error( $result ) );
	}

	/**
	 * Adds categories to an External Calendar Embeds object.
	 *
	 * @param int   $ece_id     The External Calendar Embeds object id.
	 * @param array $categories The categories to add to the External Calendar Embeds object.
	 */
	protected function add_categories_to_ece( $ece_id, $categories = [] ): void {
		$categories = array_map( function( $category ) {
			return (string) $category;
		}, $categories );

		$term_ids = [];
		foreach ( $categories as $category ) {
			$tid = term_exists( $category, TEC::TAXONOMY )['term_id'] ?? null;
			if ( $tid ) {
				$term_ids[] = $tid;
				continue;
			}
			$term_ids[] = $this->factory->term->create( [ 'slug' => $category, 'taxonomy' => TEC::TAXONOMY ] );
		}

		$result = wp_set_post_terms( $ece_id, $term_ids, TEC::TAXONOMY, true );

		$this->assertTrue( $result && ! is_wp_error( $result ) );
	}
}
