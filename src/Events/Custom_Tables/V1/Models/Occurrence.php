<?php
/**
 * The Event validation and format schema.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Generator;
use TEC\Events\Custom_Tables\V1\Events\Occurrences\Occurrences_Generator;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Date_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Integer_Key_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Numeric_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Text_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Validators\Occurrence_Duration;
use TEC\Events\Custom_Tables\V1\Models\Validators\End_Date;
use TEC\Events\Custom_Tables\V1\Models\Validators\End_Date_UTC;
use TEC\Events\Custom_Tables\V1\Models\Validators\Integer_Key;
use TEC\Events\Custom_Tables\V1\Models\Validators\Positive_Integer;
use TEC\Events\Custom_Tables\V1\Models\Validators\Start_Date;
use TEC\Events\Custom_Tables\V1\Models\Validators\Start_Date_UTC;
use TEC\Events\Custom_Tables\V1\Models\Validators\String_Validator;
use TEC\Events\Custom_Tables\V1\Models\Validators\Valid_Date;
use TEC\Events\Custom_Tables\V1\Models\Validators\Valid_Event;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Timezones as Timezones;

/**
 * Class Occurrence
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 *
 * @property Event  event
 * @property int    occurrence_id
 * @property int    event_id
 * @property int    post_id
 * @property string start_date
 * @property string start_date_utc
 * @property string end_date
 * @property string end_date_utc
 * @property int    duration
 * @property string hash
 * @property string updated_at
 * @property bool   has_recurrence (ECP only)
 * @property int    sequence  (ECP only)
 * @property int    provisional_id (ECP only)
 * @property bool   is_rdate (ECP only)
 */
class Occurrence extends Model {
	use Model_Date_Attributes;

	/**
	 * {@inheritdoc }
	 */
	protected $validations = [
		'occurrence_id'  => Integer_Key::class,
		'event_id'       => Positive_Integer::class,
		'post_id'        => Valid_Event::class,
		'start_date'     => Start_Date::class,
		'end_date'       => End_Date::class,
		'start_date_utc' => Start_Date_UTC::class,
		'end_date_utc'   => End_Date_UTC::class,
		'duration'       => Occurrence_Duration::class,
		'hash'           => String_Validator::class,
		'updated_at'     => Valid_Date::class,
	];

	/**
	 * {@inheritdoc }
	 */
	protected $formatters = [
		'occurrence_id'  => Integer_Key_Formatter::class,
		'event_id'       => Numeric_Formatter::class,
		'post_id'        => Numeric_Formatter::class,
		'start_date'     => Date_Formatter::class,
		'end_date'       => Date_Formatter::class,
		'start_date_utc' => Date_Formatter::class,
		'end_date_utc'   => Date_Formatter::class,
		'duration'       => Numeric_Formatter::class,
		'hash'           => Text_Formatter::class,
		'updated_at'     => Date_Formatter::class,
	];

	/**
	 * {@inheritdoc}
	 */
	protected $table = 'tec_occurrences';

	/**
	 * {@inheritdoc}
	 */
	protected $primary_key = 'occurrence_id';

	/**
	 * {@inheritdoc}
	 *
	 * @since 6.0.0
	 *
	 * @var string[] hashed_keys
	 */
	protected $hashed_keys = [
		'post_id',
		'start_date',
		'end_date',
		'start_date_utc',
		'end_date_utc',
		'duration',
	];

	/**
	 * Filters the Occurrence post ID to normalize it.
	 *
	 * By default the Occurrence post ID will not be modified.
	 *
	 * @since 6.0.0
	 *
	 * @param int $occurrence_id The Occurrence post ID to normalize.
	 *
	 * @return int The normalized Occurrence post ID.
	 */
	public static function normalize_id( $occurrence_id ) {
		/**
		 * Filters the Occurrence post ID to normalize it.
		 *
		 * @since 6.0.0
		 *
		 * @param int $occurrence_id The Occurrence post ID to normalize.
		 */
		$normalized_id = apply_filters( 'tec_events_custom_tables_v1_normalize_occurrence_id', $occurrence_id );

		return $normalized_id;
	}

	/**
	 * Method to save the occurrences from an event.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed $args,... The arguments that should be used to generate and save the Occurrences.
	 *
	 * @return void The method has the side-effect of generating and saving Occurrences for the Event.
	 *
	 * @throws Exception If there's an issue in the format or coherency of the additional data.
	 */
	public function save_occurrences( ...$args ) {
		$insertions = $this->get_occurrences( ...$args );

		if ( count( $insertions ) ) {
			self::insert( $insertions );

			/**
			 * Fires after Occurrences for an Event have been inserted.
			 *
			 * @since 6.0.0
			 *
			 * @param int   $post_id    The ID of the Event post the Occurrences are being saved for.
			 * @param array $insertions The inserted Occurrences.
			 */
			do_action( 'tec_events_custom_tables_v1_after_insert_occurrences', $this->event->post_id, $insertions );
		}

		/**
		 * Fires after Occurrences for an Event have been inserted, or updated, in
		 * the custom tables.
		 *
		 * @since 6.0.0
		 *
		 * @param int $post_id The ID of the Event post the Occurrences are being saved for.
		 */
		do_action( 'tec_events_custom_tables_v1_after_save_occurrences', $this->event->post_id );
	}

	/**
	 * Cast the value of the event ID to an integer if present, null otherwise when reading the `event_id` property.
	 *
	 * @since 6.0.0
	 *
	 * @param $value
	 *
	 * @return int|null
	 */
	public function get_event_id_attribute( $value ) {
		return $value ? (int) $value : null;
	}

	/**
	 * Cast the value of the property `post_id` if present to an integer.
	 *
	 * @since 6.0.0
	 *
	 * @param $value
	 *
	 * @return int|null
	 */
	public function get_post_id_attribute( $value ) {
		return $value ? (int) $value : null;
	}

	/**
	 * Dynamic accessor to the occurrence ID attribute.
	 *
	 * @since 6.0.0
	 *
	 * @param $value
	 *
	 * @return int|null
	 */
	public function get_occurrence_id_attribute( $value ) {
		return $value ? (int) $value : null;
	}

	/**
	 * If the occurrence was generated using a recurrence rule.
	 *
	 * @since 6.0.0
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function get_has_recurrence_attribute( $value ) {
		return (bool) (int) $value;
	}

	/**
	 * Returns the Occurrence model instance, if any , that starts first between all the Occurrences.
	 *
	 * @since 6.0.0
	 *
	 * @return Model|null Either the Model for the Occurrence entry that starts first, or `null`
	 *                    to indicate there are no Occurrences.
	 */
	public static function earliest() {
		$column = Timezones::is_mode( 'site' ) ? 'start_date_utc' : 'start_date';

		return self::order_by( $column )->first();
	}

	/**
	 * Returns the Occurrence mode, if any , that ends last between all the Occurrences.
	 *
	 * @since 6.0.0
	 *
	 * @return Model|null Either the Model for the Occurrence entry that ends last, or `null`
	 *                    to indicate there are no Occurrences.
	 */
	public static function latest() {
		$column = Timezones::is_mode( 'site' ) ? 'end_date_utc' : 'end_date';

		return static::order_by( $column, 'DESC' )->first();
	}

	/**
	 * Returns whether an Occurrence is the last Occurrence in context of the Recurring Event
	 * it belongs to, or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  int|Occurrence  $occurrence  Either an Occurrence `occurrence_id` or an instance of the
	 *                                      Occurrence Model.
	 *
	 * @return bool Whether an Occurrence is the first occurrence in context of the Recurring Event
	 *              it belongs to, or not.
	 */
	public static function is_last( $occurrence ) {
		$occurrence = $occurrence instanceof self
			? $occurrence
			: static::find( $occurrence, 'occurrence_id' );

		if ( ! $occurrence instanceof self ) {
			return false;
		}

		$last = self::where( 'event_id', '=', $occurrence->event_id )
		            ->order_by( 'start_date', 'DESC' )
		            ->first();

		return $last instanceof self
		       && $last->occurrence_id === $occurrence->occurrence_id;
	}

	/**
	 * Returns whether an Occurrence is the first Occurrence in context of the Recurring Event
	 * it belongs to, or not.
	 *
	 * @since 6.0.0
	 *
	 * @param  int|Occurrence  $occurrence  Either an Occurrence `occurrence_id` or an instance of the
	 *                                      Occurrence Model.
	 *
	 * @return bool Whether an Occurrence is the first occurrence in context of the Recurring Event
	 *              it belongs to, or not.
	 */
	public static function is_first( $occurrence ) {
		$occurrence = $occurrence instanceof self
			? $occurrence
			: static::find( $occurrence, 'occurrence_id' );

		if ( ! $occurrence instanceof self ) {
			return false;
		}

		$first = self::where( 'event_id', '=', $occurrence->event_id )
		             ->order_by( 'start_date_utc', 'ASC' )
		             ->order_by( 'end_date_utc', 'ASC' )
		             ->first();

		return $first instanceof self
		       && $first->occurrence_id === $occurrence->occurrence_id;
	}

	/**
	 * Calculates and returns the set of Occurrences that would be generated for the Event.
	 *
	 * This method is used internally by the `save_occurrences` method to calculate what should
	 * be inserted in the database for an Event.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed $args,...       The set of arguments that should be used to generate the
	 *                              Occurrences.
	 *
	 * @return array The set of insertions that should be performed for the Event and the
	 *               provided data.
	 *
	 * @throws Exception
	 */
	private function get_occurrences( ...$args ) {
		/**
		 * Filters the Generator that will provide the Occurrences insertions
		 * for the Event.
		 *
		 * @since 6.0.0
		 *
		 * @param Generator<Occurrence>|null $generator    A reference to the Generator that will produce
		 *                                                 the Occurrences for the data.
		 * @param mixed                      $args,...     The set of arguments to build the Generator for.
		 * @param Event $event                             A reference to the Event object Occurrences should be
		 *                                                 generated for.
		 */
		$generator = apply_filters( 'tec_events_custom_tables_v1_occurrences_generator', null, $this->event, ...$args );

		if ( ! $generator instanceof Generator ) {
			// If no generator was provided, then use the default one.
			$occurrences_generator = tribe()->make( Occurrences_Generator::class );
			$generator             = $occurrences_generator->generate_from_event( $this->event );
		}

		$post_id = $this->event->post_id;

		$insertions = [];
		$updates = [];
		$utc        = new DateTimeZone( 'UTC' );
		$first_occurrence = self::where( 'post_id', '=', $post_id )->first();
		// Clear the cache to start fresh on this upsert cycle.
		wp_cache_delete( $post_id, 'tec_occurrence_matches' );

		foreach ( $generator as $result ) {
			$occurrence = null;

			if ( isset( $first_occurrence ) && $first_occurrence instanceof self ) {
				// TEC only handles single Occurrence Events: reuse the existing one.
				$occurrence = $first_occurrence;
			}

			// Unset the first occurrence to avoid it being re-used more than once.
			unset( $first_occurrence );

			/**
			 * Filters the Occurrence that should be returned to match the requested new Occurrence.
			 *
			 * @since 6.0.0
			 *
			 * @param Occurrence|null $occurrence The Occurrence instance as returned by TEC or other
			 *                                    filtering functions.
			 * @param Occurrence      $result     A reference to the Occurrence model instance that should be inserted
			 *                                    for which a match is being searched among the existing Occurrences.
			 * @param int             $post_id    The ID of the Event post the match is being searched for.
			 */
			$occurrence = apply_filters( 'tec_custom_tables_v1_get_occurrence_match', $occurrence, $result, $post_id );

			if ( $occurrence instanceof self ) {
				$result->occurrence_id             = $occurrence->occurrence_id;
				$updated_at                        = ( new DateTime( 'now', $utc ) )->format( 'Y-m-d H:i:s' );
				$result->updated_at                = $updated_at;
				$updates[ $result->occurrence_id ] = $result->to_array();
				continue;
			}

			$insertions[] = $result->to_array();
		}

		if ( count( $updates ) ) {
			Occurrence::upsert_set( array_values( $updates ) );

			/**
			 * Fires after Occurrences for an Event have been updated.
			 *
			 * @since 6.0.0
			 *
			 * @param array $updates The updated Occurrences.
			 * @param int   $post_id The ID of the Event post the Occurrences are being saved for.
			 */
			do_action( 'tec_events_custom_tables_v1_after_update_occurrences', $this->event->post_id, $updates );
		}

		return $insertions;
	}

	/**
	 * Finds the Occurrence model instance, if any, for a real post ID, a provisional post ID,
	 * or an Occurrence ID.
	 *
	 * @param int $id The ID to return an Occurrence instance for. Either a real Event Post ID,
	 *                a provisional Occurrence ID.
	 *
	 * @return Occurrence|null A reference to the matching Occurrence instance, or `null` if
	 *                         no Occurrence instance could be matched to the ID.
	 */
	public static function find_by_post_id( $id ) {
		if ( empty( $id ) ) {
			return null;
		}

		$id = self::normalize_id( $id );

		return static::find( $id, 'post_id' );
	}

	/**
	 * Returns the Model instance `updated_at` attribute in string format.
	 *
	 * This method will be internally called when trying to access the `updated_at`
	 * property of the Model instance.
	 *
	 * @since 6.0.0
	 *
	 * @return string The Model instance `updated_at` attribute in string format.
	 */
	public function get_updated_at_attribute() {
		return $this->data['updated_at'] instanceof DateTimeInterface ?
			$this->data['updated_at']->format( Dates::DBDATETIMEFORMAT )
			: $this->data['updated_at'];
	}

	/**
	 * @since 6.0.0
	 *
	 * @param int $id Provisional or other ID that we want to validate against the database as a valid Occurrence ID.
	 *
	 * @return bool
	 */
	public static function is_valid_occurrence_id( $id ) {
		$post_id = Occurrence::normalize_id( (int) $id );

		return TEC::POSTTYPE === get_post_type( $post_id );
	}
}
