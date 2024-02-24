<?php
/**
 * The Event validation and format schema.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 */

namespace TEC\Events\Custom_Tables\V1\Models;

use TEC\Events\Custom_Tables\V1\Models\Formatters\Date_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\End_Date_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Integer_Key_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Numeric_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Text_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Formatters\Timezone_Formatter;
use TEC\Events\Custom_Tables\V1\Models\Validators\Duration;
use TEC\Events\Custom_Tables\V1\Models\Validators\End_Date;
use TEC\Events\Custom_Tables\V1\Models\Validators\End_Date_UTC;
use TEC\Events\Custom_Tables\V1\Models\Validators\Integer_Key;
use TEC\Events\Custom_Tables\V1\Models\Validators\Start_Date;
use TEC\Events\Custom_Tables\V1\Models\Validators\Start_Date_UTC;
use TEC\Events\Custom_Tables\V1\Models\Validators\String_Validator;
use TEC\Events\Custom_Tables\V1\Models\Validators\Valid_Event;
use TEC\Events\Custom_Tables\V1\Models\Validators\Valid_Timezone;
use Tribe\Events\Models\Post_Types\Event as TribeEvent;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;
use WP_Post;

/**
 * Class Event
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Models
 *
 * @property int    event_id
 * @property int    post_id
 * @property string rset (ECP only)
 * @property string start_date
 * @property string end_date
 * @property string timezone
 * @property string start_date_utc
 * @property string end_date_utc
 * @property int    duration
 * @property string updated_at
 * @property string hash
 */
class Event extends Model {
	use Model_Date_Attributes;

	/**
	 * {@inheritdoc }
	 */
	protected $validations = [
		'event_id'       => Integer_Key::class,
		'post_id'        => Valid_Event::class,
		'start_date'     => Start_Date::class,
		'end_date'       => End_Date::class,
		'timezone'       => Valid_Timezone::class,
		'duration'       => Duration::class,
		'start_date_utc' => Start_Date_UTC::class,
		'end_date_utc'   => End_Date_UTC::class,
		'hash'           => String_Validator::class,
	];

	/**
	 * {@inheritdoc }
	 */
	protected $formatters = [
		'event_id'       => Integer_Key_Formatter::class,
		'post_id'        => Numeric_Formatter::class,
		'duration'       => Numeric_Formatter::class,
		'start_date'     => Date_Formatter::class,
		'end_date'       => End_Date_Formatter::class,
		'start_date_utc' => Date_Formatter::class,
		'end_date_utc'   => End_Date_Formatter::class,
		'timezone'       => Timezone_Formatter::class,
		'hash'           => Text_Formatter::class,
	];

	/**
	 * {@inheritdoc}
	 */
	protected $table = 'tec_events';

	/**
	 * {@inheritdoc}
	 */
	protected $primary_key = 'event_id';

	/**
	 * {@inheritdoc}
	 *
	 * @since 6.0.0
	 *
	 * @var string[] hashed_keys
	 */
	protected $hashed_keys = [
		'post_id',
		'duration',
		'start_date',
		'end_date',
		'start_date_utc',
		'end_date_utc',
		'timezone',
	];

	/**
	 * Add relationship between the event and the occurrences.
	 *
	 * @since 6.0.0
	 *
	 * @return Occurrence A reference to the Occurrence model instance.
	 */
	public function occurrences() {
		return new Occurrence( [ 'event' => $this ] );
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
	 * Cast the duration of the property `duration` to an integer.
	 *
	 * @since 6.0.0
	 *
	 * @param $value
	 *
	 * @return int
	 */
	public function get_duration_attribute( $value ) {
		return (int) $value;
	}

	/**
	 * Check if the event is infinite or not, when the end date is not present (is null) it means the event
	 * is infinite.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the Event is infinite or not.
	 */
	public function is_infinite() {
		return $this->end_date === null;
	}

	/**
	 * Check if the event is a multi-day event.
	 *
	 * $since 6.0.0
	 *
	 * @return bool Whether an Event is multi-day or not.
	 */
	public function is_multiday() {
		return TribeEvent::from_post( $this->post_id )->to_post()->multiday;
	}

	/**
	 * Builds and returns, if possible, the data that should be used to hydrate an Event
	 * Model instance from the existing Event Post, from the posts table.
	 *
	 * The data provided from this method is *unvalidated* and must be passed to the Model
	 * instance for validation and sanitization.
	 *
	 * @since 6.0.0
	 *
	 * @param int|WP_Post $event_id Either the Event Post ID, or a reference to the Event
	 *                              Post object.
	 *
	 * @return array<string,mixed> Either an array of the Event Model data read from the
	 *                             existing post, or an empty array if the post data could
	 *                             not be read for any reason.
	 */
	public static function data_from_post( $event_id ) {
		$post = get_post( $event_id );

		if ( ! $post instanceof WP_Post || $post->post_type !== TEC::POSTTYPE ) {
			return [];
		}

		$post_id = $post->ID;

		$start_date_utc = get_post_meta( $post_id, '_EventStartDateUTC', true );
		$end_date_utc = get_post_meta( $post_id, '_EventEndDateUTC', true );
		$duration = get_post_meta( $post_id, '_EventDuration', true );

		if ( empty( $duration ) && ! empty( $start_date_utc ) && ! empty( $end_date_utc ) ) {
			$start_date_object = Dates::immutable( $start_date_utc, 'UTC' );
			$end_date_object = Dates::immutable( $end_date_utc, 'UTC' );
			$duration = $end_date_object->getTimestamp() - $start_date_object->getTimestamp();
		}

		$data = [
			'post_id'        => $post_id,
			'start_date'     => get_post_meta( $post_id, '_EventStartDate', true ),
			'end_date'       => get_post_meta( $post_id, '_EventEndDate', true ),
			'timezone'       => get_post_meta( $post_id, '_EventTimezone', true ),
			'duration'       => $duration,
			'start_date_utc' => $start_date_utc,
			'end_date_utc'   => $end_date_utc,
			'hash'           => '',
		];

		/**
		 * Filters the data that will be returned to hydrate an Event model.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,mixed> $data     The data for the Event, as prepared by The
		 *                                      Events Calendar and previous filters.
		 * @param int                 $post_id The Event post ID.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_event_data_from_post', $data, $post_id );
	}

	/**
	 * Returns the value of a model field.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $post_id The Event post ID to return the value for.
	 * @param string $field   The name of the Event model property to return the value for.
	 *
	 * @return mixed|null Either the field value, or the default value if not found.
	 */
	public static function get_field( $post_id, $field, $default = null ) {
		$model = static::find( $post_id, 'post_id' );
		if ( ! $model instanceof static ) {
			return null;
		}

		return $model->{$field};
	}
}
