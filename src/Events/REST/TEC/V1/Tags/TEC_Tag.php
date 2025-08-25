<?php
/**
 * TEC tag for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Tags
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Tags;

use TEC\Common\REST\TEC\V1\Abstracts\Tag;

/**
 * TEC tag for the TEC REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Tags
 */
class TEC_Tag extends Tag {
	/**
	 * Returns the name of the tag.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'Events';
	}

	/**
	 * Returns the tag.
	 *
	 * @since 6.15.0
	 *
	 * @return array
	 */
	public function get(): array {
		return [
			'name'        => $this->get_name(),
			'description' => __( 'These operations are introduced by The Events Calendar.', 'the-events-calendar' ),
		];
	}

	/**
	 * Returns the priority of the tag.
	 *
	 * @since 6.15.0
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 1;
	}
}
