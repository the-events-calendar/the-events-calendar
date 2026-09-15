<?php
/**
 * The strategy used to migrate an Event whose recurrence is a list of explicit dates
 * (date rules only, no recurrence rule patterns) to the Custom Tables v1 format.
 *
 * Rule-based recurring Events are NOT handled here: without Events Calendar Pro they
 * keep failing migration with the message pointing at it.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Migration
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Expected_Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Traits\With_String_Dictionary;
use TEC\Events\Recurrence\Date_Rules;
use TEC\Events\Recurrence\Dates;
use Tribe__Events__Main as TEC;

/**
 * Class Date_Rules_Migration_Strategy.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Migration
 */
class Date_Rules_Migration_Strategy implements Strategy_Interface {
	use With_String_Dictionary;

	/**
	 * The post ID of the Event to migrate.
	 *
	 * @since TBD
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Whether the migration should actually commit information or run in dry-run mode.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $dry_run;

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return 'tec-date-rules-strategy';
	}

	/**
	 * Date_Rules_Migration_Strategy constructor.
	 *
	 * The guards are defense-in-depth: the strategy loader only selects this strategy
	 * for Events matching them, and any exception thrown here fails the Event migration.
	 *
	 * @since TBD
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should actually commit information,
	 *                      or run in dry-run mode.
	 *
	 * @throws Migration_Exception If the post is not an Event, or its recurrence meta
	 *                             is not a list of explicit dates.
	 */
	public function __construct( $post_id, $dry_run ) {
		$this->post_id = (int) $post_id;

		if ( TEC::POSTTYPE !== get_post_type( $this->post_id ) ) {
			throw new Migration_Exception( 'Post is not an Event.' );
		}

		$recurrence_meta = get_post_meta( $this->post_id, '_EventRecurrence', true );

		if ( empty( $recurrence_meta ) ) {
			throw new Migration_Exception( 'Event has no recurrence meta: the Single Event strategy applies.' );
		}

		if ( ! Date_Rules::is_dates_only_meta( $recurrence_meta ) ) {
			throw new Migration_Exception( 'Event recurrence is not a list of explicit dates.' );
		}

		$this->dry_run = (bool) $dry_run;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Expected_Migration_Exception When the Event upsertion fails.
	 * @throws Migration_Exception When the migrated data does not match the expectations.
	 */
	public function apply( Event_Report $event_report ) {
		$upserted = Event::upsert( [ 'post_id' ], Event::data_from_post( $this->post_id ) );

		if ( false === $upserted ) {
			$errors       = Event::last_errors();
			$error_string = implode( '. ', $errors );
			$text         = tribe( String_Dictionary::class );

			$message = sprintf(
				$text->get( 'migration-error-k-upsert-failed' ),
				$this->get_event_link_markup( $this->post_id ),
				$error_string,
				'<a target="_blank" href="https://evnt.is/migrationhelp">',
				'</a>'
			);

			throw new Expected_Migration_Exception( $message );
		}

		if ( $this->dry_run && 0 === $upserted ) {
			// Transactions are not supported, it did not explode: enough preview.
			return $event_report->add_strategy( self::get_slug() )
								->set( 'is_single', false )
								->migration_success();
		}

		$event_model = Event::find( $this->post_id, 'post_id' );

		if ( ! $event_model instanceof Event ) {
			throw new Migration_Exception( 'Event model could not be found.' );
		}

		if ( '' === trim( (string) $event_model->rset ) ) {
			// The dates RSET derivation runs while building the Event data: it should be set.
			throw new Migration_Exception( 'Event model does not have an RSET: it should at this stage.' );
		}

		$event_model->occurrences()->save_occurrences();

		$parsed = Dates::parse( (string) $event_model->rset, (int) ( $event_model->duration ?: 7200 ) );

		if ( null === $parsed ) {
			throw new Migration_Exception( 'The derived Event RSET could not be parsed back.' );
		}

		// The parsed periods are deduplicated by start and include the Event date itself.
		$expected    = count( $parsed['periods'] );
		$occurrences = Occurrence::where( 'post_id', '=', $this->post_id )->count();

		if ( $occurrences !== $expected ) {
			throw new Migration_Exception(
				sprintf(
					'Unexpected number of Occurrences found: expected %d, found %d.',
					$expected,
					$occurrences
				)
			);
		}

		return $event_report->add_strategy( self::get_slug() )
							->set( 'is_single', false )
							->migration_success();
	}
}
