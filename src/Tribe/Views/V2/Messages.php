<?php
/**
 * Handles a collection of View Messages.
 *
 * @since   4.9.11
 *
 * @package Tribe\Events\Views\V2
 */

namespace Tribe\Events\Views\V2;

use Tribe__Utils__Array as Arr;

/**
 * Class Messages
 *
 * @since   4.9.11
 *
 * @package Tribe\Events\Views\V2
 */
class Messages {
	/**
	 * A notice type of message.
	 *
	 * @since 4.9.11
	 */
	const TYPE_NOTICE = 'notice';

	/**
	 * The strategy that will print a single message, the last, per priority collection, per type.
	 *
	 * @since 4.9.11
	 */
	const RENDER_STRATEGY_PRIORITY_LAST = 'priority_last';

	/**
	 * The strategy that will print a single message, the first, per priority collection, per type.
	 *
	 * @since 4.9.11
	 */
	const RENDER_STRATEGY_PRIORITY_FIRST = 'priority_first';

	/**
	 * The strategy that will print all messages, of all types.
	 *
	 * @since 4.9.11
	 */
	const RENDER_STRATEGY_LIST = 'list';

	/**
	 * An array of the messages handled by the object.
	 *
	 * @since 4.9.11
	 *
	 * @var array
	 */
	protected $messages = [];

	/**
	 * The render strategy the collection will use to "render" the messages in the `to_array` method.
	 *
	 * @since 4.9.11
	 *
	 * @var string
	 */
	protected $render_strategy;

	/**
	 * Messages constructor.
	 *
	 * @param null|string $render_strategy The render strategy that should be used to render the messages in the
	 *                                     `to_array` method.
	 * @param array       $messages A list of messages to hydrate the collection with.
	 */
	public function __construct( $render_strategy = null, array $messages = [] ) {
		$this->render_strategy = $render_strategy ?: static::RENDER_STRATEGY_PRIORITY_LAST;
		$this->messages        = $messages;
	}

	/**
	 * Returns the human-readable message for a key.
	 *
	 * @since 4.9.11
	 *
	 * @param string $key The message identification key or slug.
	 * @param mixed  ...$values A variadic number of arguments that should be used to fill in the message placeholders, if
	 *                       the message contains `sprintf` compatible placeholders at all.
	 *
	 * @return string The human readable message for the specified key, if found, or the key itself.
	 */
	public static function for_key( $key, ...$values ) {
		$map = [
			'no_results_found'                 => __(
				'There were no results found.',
				'the-events-calendar'
			),
			'no_upcoming_events'               => sprintf(
			/* Translators: %1$s is the lowercase plural virtual event term. */
				_x(
					'There are no upcoming %1$s.',
					'A message to indicate there are no upcoming events.',
					'the-events-calendar'
				),
				tribe_get_event_label_plural_lowercase()
			),
			'month_no_results_found'           => __(
				'There were no results found for this view.',
				'the-events-calendar'
			),
			// translators: the placeholder is the keyword(s), as the user entered it in the bar.
			'no_results_found_w_keyword'       => __(
				'There were no results found for <strong>"%1$s"</strong>.',
				'the-events-calendar'
			),
			// translators: the placeholder is the keyword(s), as the user entered it in the bar.
			'month_no_results_found_w_keyword' => __(
				'There were no results found for <strong>"%1$s"</strong> this month.',
				'the-events-calendar'
			),
			// translators: %1$s: events (plural), %2$s: the formatted date string, e.g. "February 22, 2020".
			'day_no_results_found'             => __(
				'No %1$s scheduled for %2$s.',
				'the-events-calendar'
			),
			// translators: the placeholder is an html link to the next month with available events.
			'month_no_results_found_w_ff_link' => __(
				'There were no results found for this view. %1$s',
				'the-events-calendar'
			),
			// translators: %1$s: events (plural), %2$s: the formatted date string, e.g. "February 22, 2020". %3$s html link to next day with available events.
			'day_no_results_found_w_ff_link'   => __(
				'No %1$s scheduled for %2$s. %3$s',
				'the-events-calendar'
			),
		];

		/**
		 * Filters the map of user-facing messages that will be used in the Views.
		 *
		 * @since 4.9.11
		 *
		 * @param array $map An map of message keys to localized, user-facing, messages.
		 */
		$map = apply_filters( 'tribe_events_views_v2_messages_map', $map );

		// If not found return the key itself.
		$match = Arr::get( $map, $key, $key );

		if ( empty( count( $values ) ) ) {
			return $match;
		}

		$need_events_label_keys = [ 'day_no_results_found', 'day_no_results_found_w_ff_link' ];

		/**
		 * Filters the array of keys of the messages that need the events label.
		 *
		 * @since 5.0.3
		 *
		 * @param array $need_events_label_keys Array of keys of the messages that need events label.
		 */
		$need_events_label_keys = apply_filters( 'tribe_events_views_v2_messages_need_events_label_keys', $need_events_label_keys );

		if ( in_array( $key, $need_events_label_keys ) ) {
			array_unshift( $values, tribe_get_event_label_plural_lowercase() );
		}

		return sprintf( $match, ...$values );
	}

	/**
	 * Applies the current message render policy to the messages and returns an array of messages.
	 *
	 * @since 4.9.11
	 *
	 * @return array An array of messages in the shape `[ <message_type> => [ ...<messages> ] ]`.
	 */
	public function to_array() {
		return $this->apply_render_strategy( $this->messages );
	}

	/**
	 * Applies the render strategy to the collection of messages.
	 *
	 * @since 4.9.11
	 *
	 * @param array $messages The collection of messages to apply the render strategy to.
	 *
	 * @return array An array of messages after the current strategy application.
	 *               No matter the render strategy, the array always has shape
	 *              `[ <message_type> => [ ...<messages> ] ]`.
	 */
	protected function apply_render_strategy( array $messages = [] ) {
		if ( empty( $messages ) ) {
			return [];
		}

		$updated_messages = $this->messages;

		switch ( $this->render_strategy ) {
			case static::RENDER_STRATEGY_PRIORITY_LAST:
				array_walk(
					$updated_messages,
					static function ( array &$value, $message_type ) {
						ksort( $value );
						// Keep the highest priority (lower number).
						$highest = array_filter( (array) reset( $value ) );
						// Keep only the last message.
						$value = ! empty( $highest ) ? [ end( $highest ) ] : [];
					}
				);
				break;
			case static::RENDER_STRATEGY_PRIORITY_FIRST:
				array_walk(
					$updated_messages,
					static function ( array &$value, $message_type ) {
						ksort( $value );
						// Keep the highest priority (lower number).
						$highest = array_filter( (array) reset( $value ) );
						// Keep only the first message.
						$value = ! empty( $highest ) ? [ reset( $highest ) ] : [];
					}
				);
				break;
			default:
			case static::RENDER_STRATEGY_LIST:
			array_walk(
				$updated_messages,
				static function ( array &$value, $message_type ) {
					ksort( $value );
				}
			);
			break;
		}

		// Remove empty entries.
		return array_filter( $updated_messages );
	}

	/**
	 * Sets the render strategy that the collection should use to render the messages in the `to_array` method.
	 *
	 * @since 4.9.11
	 *
	 * @param string $render_strategy One of the `RENDER_STRATEGY_` constants.
	 */
	public function set_render_strategy( $render_strategy ) {
		$this->render_strategy = $render_strategy;
	}

	/**
	 * Inserts a message in the collection, at a specific priority.
	 *
	 * @since 4.9.11
	 *
	 * @param string $message_type    The type of message to insert, while there is no check on the type, the suggestion
	 *                                is to use one of the `TYPE_` constants.
	 * @param string $message         The message to insert.
	 * @param int    $priority        the priority of the message among the types; defaults to `10`. Similarly to the
	 *                                priority concept of WordPress filters, an higher number has a lower priority.
	 */
	public function insert( $message_type, $message, $priority = 10 ) {
		if ( empty( $this->messages[ $message_type ][ $priority ] ) ) {
			$this->messages[ $message_type ][ $priority ] = [ $message ];

			return;
		}
		$this->messages[ $message_type ][ $priority ][] = $message;
	}

	/**
	 * Resets a specific type of messages or all of them.
	 *
	 * @since 4.9.11
	 *
	 * @param null|string $type     The type of message to reset, or `null` to reset all messages.
	 * @param null|int    $priority The specific priority to reset, this will be ignored if the `$type` parameter
	 *                              is not set.
	 */
	public function reset( $type = null, $priority = null ) {
		if ( null !== $type && isset( $this->messages[ $type ] ) ) {
			if ( null !== $priority ) {
				if ( isset( $this->messages[ $type ][ $priority ] ) ) {
					unset( $this->messages[ $type ][ $priority ] );
				}
			} else {
				unset( $this->messages[ $type ] );
			}
		} else {
			$this->messages = [];
		}
	}
}
