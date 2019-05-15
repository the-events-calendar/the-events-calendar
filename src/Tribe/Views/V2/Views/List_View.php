<?php
/**
 * The List View.
 *
 * @package Tribe\Events\Views\V2\Views
 * @since 4.9.2
 */

namespace Tribe\Events\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Utils__Array as Arr;

class List_View extends View {

	public function get_html() {
		/*
		 * The View not care where the context comes from: from the View point of view the context is the only
		 * source of truth.
		 * The context might come from the main query, from a widget, a shortcode or a REST request.
		 */
		$context = $this->context->to_array();

		/*
		 * Depending on the context contents let's set up the arguments to fetch the events.
		 */
		$args    = [
			'posts_per_page' => $context['posts_per_page'],
			'paged'          => Arr::get( $context, 'page', 1 ),
		];
		$date = Arr::get( $context, 'eventDate', 'now' );

		if ( 'past' !== Arr::get( $context, 'event_display', 'current' ) ) {
			$args['ends_after'] = $date;
		} else {
			$args['ends_before'] = $date;
		}

		/*
		 * After we built the args to query the Events Repository with we use them to fetch the matching events.
		 */
		$events = tribe_events()->by_args( $args )->all();

		/*
		 * Here we pass to the template a trimmed down version of the View render context.
		 * Ideally one that contains only the variables the template will need to render.
		 */
		return $this->template->render( [
			'events' => $events,
		] );
	}
}
