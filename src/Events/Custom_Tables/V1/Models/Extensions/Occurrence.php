<?php
/**
 * Provides the code required to extend the base Occurrence Model using the extensions API.
 *
 * Adapted from `TEC\Events_Pro\Custom_Tables\V1\Models\Occurrence`: the
 * `get_single_edit_post_link` method is still provided by Events Calendar Pro and will
 * move here together with the per-Occurrence links.
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Extensions
 */

namespace TEC\Events\Custom_Tables\V1\Models\Extensions;

use TEC\Events\Custom_Tables\V1\Events\Provisional\ID_Generator;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Boolean_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Integer_Key_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Occurrence as Occurrence_Model;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events\Custom_Tables\V1\Models\Validators\Ignore_Validator;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe__Cache as Cache;

/**
 * Class Occurrence
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Models\Extensions
 */
class Occurrence {

	/**
	 * The provisional post.
	 *
	 * @since TBD
	 *
	 * @var Provisional_Post
	 */
	private $provisional_post;

	/**
	 * The cache.
	 *
	 * @since TBD
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * The ID generator.
	 *
	 * @since TBD
	 *
	 * @var ID_Generator
	 */
	private $id_generator;

	/**
	 * Occurrence extension constructor.
	 *
	 * @since TBD
	 *
	 * @param Provisional_Post $provisional_post A reference to the Provisional Post handler.
	 * @param ID_Generator     $id_generator     A reference to the provisional ID generator.
	 * @param Cache            $cache            A reference to the cache handler.
	 */
	public function __construct( Provisional_Post $provisional_post, ID_Generator $id_generator, Cache $cache ) {
		$this->provisional_post = $provisional_post;
		$this->cache            = $cache;
		$this->id_generator     = $id_generator;
	}

	/**
	 * Extends the Occurrence base model to add the fields backing multi-Occurrence Events.
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
		 * wins on the same key, no matter the order the extensions apply in.
		 */
		$extensions['validators'] = wp_parse_args(
			$extensions['validators'] ?? [],
			[
				'has_recurrence' => Ignore_Validator::class,
				'sequence'       => Ignore_Validator::class,
				'is_rdate'       => Ignore_Validator::class,
			]
		);

		$extensions['formatters'] = wp_parse_args(
			$extensions['formatters'] ?? [],
			[
				'has_recurrence' => Boolean_Formatter::class,
				'sequence'       => Integer_Key_Formatter::class,
				'is_rdate'       => Boolean_Formatter::class,
			]
		);

		$extensions['properties'] = wp_parse_args(
			$extensions['properties'] ?? [],
			[
				'provisional_id' => [ $this, 'get_provisional_id' ],
				'is_rdate'       => [ $this, 'get_is_rdate' ],
			]
		);

		return $extensions;
	}

	/**
	 * Normalizes an Occurrence post ID taking Provisional Post IDs into
	 * account.
	 *
	 * @since TBD
	 *
	 * @param int $id The Occurrence post ID to normalize.
	 *
	 * @return int The normalized Occurrence post ID.
	 */
	public function normalize_occurrence_post_id( int $id ): int {
		if ( ! $this->provisional_post->is_provisional_post_id( $id ) ) {
			return $id;
		}

		$occurrence = Occurrence_Model::find(
			$this->provisional_post->normalize_provisional_post_id( $id ),
			'occurrence_id'
		);

		return $occurrence instanceof Occurrence_Model ? $occurrence->post_id : $id;
	}

	/**
	 * Fetches the sequence value for an Event Occurrences.
	 *
	 * Note: the first, valid, sequence value is `1`. A value of `0` indicates
	 * no sequence was found.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID to fetch the sequence for.
	 *
	 * @return int The sequence value, or `0` if no sequence could be found.
	 */
	public static function get_sequence( int $post_id ): int {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );
		$sequence    = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name cannot be a placeholder.
				"SELECT MAX(sequence) FROM $occurrences WHERE post_id = %d",
				$post_id
			)
		);

		return empty( $sequence ) ? 0 : (int) $sequence;
	}

	/**
	 * Returns an Occurrence provisional post ID.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $model_data The Occurrence model data, as provided by
	 *                                        the base Model.
	 *
	 * @return int|null The Occurrence provisional post ID, or `null` if not found.
	 */
	public function get_provisional_id( array $model_data ): ?int {
		return isset( $model_data['occurrence_id'] ) ?
			$this->id_generator->provide_id( $model_data['occurrence_id'] )
			: null;
	}

	/**
	 * Returns whether an Occurrence is an RDATE or not.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $model_data The Occurrence model data, as provided by
	 *                                        the base Model.
	 *
	 * @return bool Whether the Occurrence is an RDATE or not.
	 */
	public function get_is_rdate( array $model_data ): bool {
		return ! empty( $model_data['is_rdate'] );
	}
}
