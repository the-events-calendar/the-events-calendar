<?php
/**
 * Content for a calendar embed.
 *
 * @since 6.11.0
 *
 * @version 6.11.0
 *
 * @var int   $calendar_embed_id The ID of the calendar embed.
 * @var array $event_categories  The event categories.
 * @var array $event_tags        The event tags.
 */

use Tribe__Events__Main as TEC;
use TEC\Events\Calendar_Embeds\Render;

defined( 'ABSPATH' ) || exit;

// // Make use of the views cache by correctly setting up the context which is being used for cache key generation.
add_filter( 'tribe_context_pre_eventDisplay', static fn() => 'month' );
add_filter( 'tribe_context_pre_post_tag', static fn() => $event_tags ? wp_list_pluck( $event_tags, 'term_id' ) : null );
add_filter( 'tribe_context_pre_' . TEC::TAXONOMY, static fn() => $event_categories ? wp_list_pluck( $event_categories, 'term_id' ) : null );

$render = new Render();
$render->setup(
	[
		'view'        => 'month',
		TEC::TAXONOMY => $event_categories ? wp_list_pluck( $event_categories, 'term_id' ) : null,
		'tag'         => $event_tags ? wp_list_pluck( $event_tags, 'term_id' ) : null,
		'tribe-bar'   => false,
		'hide-export' => true,
	],
);

// phpcs:ignore StellarWP.XSS.EscapeOutput.OutputNotEscaped, WordPress.Security.EscapeOutput.OutputNotEscaped
echo $render->get_html();
