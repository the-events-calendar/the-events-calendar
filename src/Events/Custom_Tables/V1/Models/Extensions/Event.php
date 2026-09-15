<?php
/**
 * Provides the code required to extend the base Event Model using the extensions API.
 *
 * The free extension adds tolerant handling of the `rset` field: the free plugin authors
 * and validates dates-only RSETs, and preserves, without judging them, the rule-based
 * RSETs authored by Events Calendar Pro. Pro's own extension, when active, composes over
 * this one and upgrades the validation to a full RSET one.
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Extensions
 */

namespace TEC\Events\Custom_Tables\V1\Models\Extensions;

use TEC\Events\Custom_Tables\V1\Models\Formatters\Rset_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Validators\Ignore_Validator;

/**
 * Class Event
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Extensions
 */
class Event {
	/**
	 * Extends the Event base model to add the `rset` field.
	 *
	 * @since TBD
	 *
	 * @param array<string,array<string,mixed>> $extensions A map of the current Model
	 *                                                      extensions.
	 *
	 * @return array<string,array<string,mixed>> The filtered extensions map.
	 */
	public function extend( array $extensions = [] ): array {
		/*
		 * The free entries are defaults: a plugin providing a richer implementation
		 * (e.g. Events Calendar Pro's full RSET validation) wins on the same key, no
		 * matter the order the extensions apply in.
		 */
		$extensions['validators'] = wp_parse_args(
			$extensions['validators'] ?? [],
			[ 'rset' => Ignore_Validator::class ]
		);

		$extensions['formatters'] = wp_parse_args(
			$extensions['formatters'] ?? [],
			[ 'rset' => Rset_Formatter::class ]
		);

		$extensions['hashed_keys'] = array_values(
			array_unique( array_merge( $extensions['hashed_keys'] ?? [], [ 'rset' ] ) )
		);

		$extensions['methods'] = wp_parse_args(
			$extensions['methods'] ?? [],
			[
				'has_recurrence' => function () {
					/** @var \TEC\Events\Custom_Tables\V1\Models\Event $this Bound at run time to the Closure. */
					return ! empty( $this->rset );
				},
			]
		);

		return $extensions;
	}
}
