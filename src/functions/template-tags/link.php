<?php
/**
 * Link Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

/**
 * Displays a link to the previous event by start date.
 *
 * The link text can be customized by passing a string to the `$anchor` parameter.
 *
 * @since 3.0
 *
 * @param bool|string $anchor Optional. Custom link text. Use `%title%` to place the event title in the string. Default is false.
 *
 * @return void
 *
 * @see tribe_get_prev_event_link()
 */
function tribe_the_prev_event_link( $anchor = false ) {
	echo apply_filters( 'tribe_the_prev_event_link', tribe_get_prev_event_link( $anchor ) );
}

/**
 * Returns a link to the previous event by start date for the given event.
 *
 * @since 5.14.0
 *
 * @param  bool|string $anchor (optional) The link text. Use %title% to place the post title in your string. Default is false.
 *
 * @return string              The link to the previous event.
 */
function tribe_get_prev_event_link( $anchor = false ) {
	$event_id = get_the_ID();

	tribe( 'tec.adjacent-events' )->set_current_event_id( $event_id );

	/**
	 * Filter the output of the link to the previous event by start date of a given event.
	 *
	 * @param string $prev_event_link The link to the previous event.
	 * @param int    $event_id        The ID of the reference event.
	 */
	return apply_filters( 'tribe_get_prev_event_link', tribe( 'tec.adjacent-events' )->get_prev_event_link( $anchor ), $event_id );
}

/**
 * Displays a link to the next event by start date.
 *
 * The link text can be customized by passing a string to the `$anchor` parameter.
 *
 * @since 3.0
 *
 * @param bool|string $anchor Optional. Custom link text. Use `%title%` to place the event title in the string. Default is false.
 *
 * @return void
 *
 * @see tribe_get_next_event_link()
 */
function tribe_the_next_event_link( $anchor = false ) {
	echo apply_filters( 'tribe_the_next_event_link', tribe_get_next_event_link( $anchor ) );
}

/**
 * Returns a link to the next event by start date for the given event.
 *
 * @since 5.14.0
 *
 * @param bool|string $anchor (optional) The link text. Use %title% to place the post title in your string. Default is false.
 *
 * @return string The link to the next event.
 */
function tribe_get_next_event_link( $anchor = false ) {
	$event_id = get_the_ID();

	tribe( 'tec.adjacent-events' )->set_current_event_id( $event_id );

	/**
	 * Filter the output of the link to the next event by start date of a given event.
	 *
	 * @param string $next_event_link The link to the next event.
	 * @param int    $event_id        The ID of the reference event.
	 */
	return apply_filters( 'tribe_get_next_event_link', tribe( 'tec.adjacent-events' )->get_next_event_link( $anchor ), $event_id );
}

/**
 * Retrieves the URL for the previous events page.
 *
 * This function generates a link to the previous events, considering whether the user is on the upcoming events view
 * and which page they are currently on.
 *
 * @since 3.7
 *
 * @return string The URL to the previous events page.
 */
function tribe_get_previous_events_link() {

	$link = '';

	if ( tribe_is_upcoming() && ( ! empty ( $_REQUEST['tribe_paged'] ) && $_REQUEST['tribe_paged'] > 1 ) ) {
		// if we're more than one page into the future, the previous link will be in the future as well
		$link = tribe_get_upcoming_link();
	} else {
		$link = tribe_get_past_link();
	}

	return apply_filters( 'tribe_get_previous_events_link', $link );
}

/**
 * Get a link to the next events
 *
 * @return string
 */
function tribe_get_next_events_link() {

	$link = '';

	if ( tribe_is_past() && ( ! empty ( $_REQUEST['tribe_paged'] ) && $_REQUEST['tribe_paged'] > 1 ) ) {
		// if we're more than one page into the past, the next link will be in the past as well
		$link = tribe_get_past_link();
	} else {
		$link = tribe_get_upcoming_link();
	}

	return apply_filters( 'tribe_get_next_events_link', $link );
}

/**
 * Get a link to all events.
 *
 * Returns the main URL for the events. The URL can be optionally returned in a format suitable for display,
 * where non-Latin characters are not URL-encoded.
 *
 * @since 2.0.1
 *
 * @param  string $context Optional. Defaults to 'href'. Can be 'display', in which case non-Latin characters are not URL-encoded.
 *
 * @return string          The URL to the events page.
 *
 * @hook   tribe_get_events_link Filters the main events link.
 */
function tribe_get_events_link( $context = 'href' ) {
	$plugin = Tribe__Events__Main::instance();
	/**
	 * Allows for filtering the main events link.
	 *
	 * Returns a link to the events URL
	 *
	 * @since 3.8
	 *
	 * @param string $link The main events link.
	 * @param string $context Defaults to 'href'. Can also be 'display', in which case non-latin chars are not url-encoded.
	 */
	return apply_filters( 'tribe_get_events_link', $plugin->getLink( 'home' ), $context );
}

/**
 * Gets a view permalink.
 *
 * Generates the permalink for a specific view based on the provided slug and optional term.
 *
 * @since 5.7.0
 *
 * @hook tribe_get_view_permalink        Filters the overall view permalink.
 * @hook tribe_get_{slug}_view_permalink Filters the specific view permalink.
 *
 * @param string        $slug      The slug of the view for which the permalink is being generated.
 * @param bool|int|null $term      Optional. The term associated with the view. Default is null.
 *
 * @return string       $permalink The generated permalink for the view.
 */
function tribe_get_view_permalink( $slug, $term = null ) {
	$permalink = tribe_events_get_url( $slug );

	/**
	 * Provides an opportunity to modify the overall view permalink.
	 *
	 * @var string $permalink
	 * @var string $slug
	 */
	$permalink = apply_filters( 'tribe_get_view_permalink', $permalink, $slug );

	/**
	 * Provides an opportunity to modify the specific view permalink.
	 *
	 * @var string $permalink
	 * @var string $slug
	 */
	return apply_filters( "tribe_get_{$slug}_view_permalink", $permalink, $slug );
}

/**
 * Get a link to the Grid View.
 *
 * @hook tribe_get_gridview_link Filters the URL to the grid view.
 *
 * @since 2.0.1
 *
 * @param  string|null $term   Optional. Event category to link to. Default is null.
 *
 * @return string      $output The generated URL to the grid view.
 */
function tribe_get_gridview_link( $term = null ) {
	$tribe_ecp = Tribe__Events__Main::instance();
	$output    = $tribe_ecp->getLink( \Tribe\Events\Views\V2\Views\Month_View::get_view_slug(), false, $term );

	return apply_filters( 'tribe_get_gridview_link', $output );
}

/**
 * Returns a link to the general or category upcoming view.
 *
 * @since  2.0.1
 *
 * @param  int|null $term Optional. Event category ID to link to. Default null.
 * @return string The URL of the list view.
 */
function tribe_get_listview_link( $term = null ) {
	$tribe_ecp = Tribe__Events__Main::instance();
	$output    = $tribe_ecp->getLink( \Tribe\Events\Views\V2\Views\List_View::get_view_slug(), false, $term );

	return apply_filters( 'tribe_get_listview_link', $output );
}

/**
 * Returns a url to the general or category past view.
 *
 * @since 2.0.1
 *
 * @param int|null $term Optional. Term ID. Default null.
 *
 * @return string URL of the past list view.
 */
function tribe_get_listview_past_link( $term = null ) {
	$tribe_ecp = Tribe__Events__Main::instance();
	$output    = $tribe_ecp->getLink( 'past', false, $term );

	return apply_filters( 'tribe_get_listview_past_link', $output );
}

/**
 * Returns a link to the next or previous list view page.
 *
 * @since 4.0
 *
 * @param string      $direction            'next' or 'prev'.
 * @param int|null    $term                 Optional. Term ID. Default null.
 * @param string|null $currently_displaying Optional. Type of list view page currently displayed ('past' or 'list'). Default null.
 * @param int|null    $page                 Optional. Current page number being displayed. Default null.
 *
 * @return string URL of the next or previous list view page.
 */
function tribe_get_listview_dir_link( $direction = 'next', $term = null, $currently_displaying = null, $page = null ) {
	$link = tribe_get_listview_link( $term );

	// if a page isn't passed in, attempt to fetch it from a get var
	if ( ! $page ) {
		$page = absint( tribe_get_request_var( 'tribe_paged', 1 ) );
	}

	$args = tribe_get_listview_args( $page, $direction, $currently_displaying );
	$link = add_query_arg(
		[
			'tribe_event_display' => $args['display'],
			'tribe_paged'         => absint( $args['page'] ),
		],
		$link
	);

	return apply_filters( 'tribe_get_listview_dir_link', $link, $term );
}

/**
 * Updates pagination and determines the current view display for the list view.
 *
 * This function adjusts the pagination and sets the view to be displayed based on
 * the current page and navigation direction.
 *
 * @since 4.6.12
 *
 * @param int         $page                 The current page number. Default is 1.
 * @param string      $direction            The navigation direction, either 'next' or 'prev'. Default is 'next'.
 * @param string|null $currently_displaying The view currently being displayed, such as 'list' or 'past'.
 *                                          Default is null, which triggers a fallback to the default list view.
 *
 * @return array {
 *      An associative array containing the updated display and page number.
 *
 *      @type string $display The view to be displayed, either 'list' or 'past'.
 *      @type int    $page    The updated page number.
 * }
 */
function tribe_get_listview_args( $page = 1, $direction = 'next', $currently_displaying = null ) {
	// if what we are currently displaying is not passed in, let's set a default and check $_GET
	if ( ! $currently_displaying ) {
		$currently_displaying = tribe_get_listview_display();
	}

	// assume we want to display what we're currently displaying (until we discover otherwise)
	$display        = $currently_displaying;
	$list_view_slug = \Tribe\Events\Views\V2\Views\List_View::get_view_slug();

	if (
		( 'next' === $direction && $list_view_slug === $currently_displaying )
		|| ( 'prev' === $direction && 'past' === $currently_displaying )
	) {
		$page++;
	} elseif ( $list_view_slug === $currently_displaying && 1 === $page ) {
		$display = 'past';
	} elseif ( 'past' === $currently_displaying && 1 === $page ) {
		$display = $list_view_slug;
	} else {
		$page--;
	}

	return [
		'display' => $display,
		'page'    => $page,
	];
}

/**
 * Retrieves the display view for the events, defaulting to the list view if an invalid view is requested.
 *
 * This function checks whether the current view is one of the two allowed views: the list view or the past view.
 * If the requested view is not valid, it defaults to the list view.
 *
 * @since 4.6.12
 *
 * @return string The validated display view, either 'list' or 'past'.
 */
function tribe_get_listview_display() {
	$view_slug       = \Tribe\Events\Views\V2\Views\List_View::get_view_slug();
	$default_display = $view_slug;
	$display         = tribe_get_request_var( 'tribe_event_display', $default_display );
	$valid_values    = [ $view_slug, 'past' ];

	return in_array( $display, $valid_values ) ? $display : $default_display;
}


/**
 * Retrieves the URL for the previous page in the list view.
 *
 * This function generates a link to the previous page of events in the list view,
 * optionally filtered by a specific taxonomy term.
 * The returned URL can be modified via the `tribe_get_listview_prev_link` filter.
 *
 * @since 3.11
 *
 * @param int|null $term Optional. The term ID for filtering events by a specific taxonomy term.
 *                       Defaults to null, meaning no specific term filtering.
 *
 * @return string The URL to the previous page of the list view.
 */
function tribe_get_listview_prev_link( $term = null ) {
	$link = tribe_get_listview_dir_link( 'prev', $term );
	return apply_filters( 'tribe_get_listview_prev_link', $link, $term );
}

/**
 * Retrieves the URL for the next page in the list view.
 *
 * This function returns a link to the next page of events in the list view,
 * optionally filtered by a specific taxonomy's term.
 * The returned link can be filtered using the `tribe_get_listview_next_link` filter.
 *
 * @since 3.11
 *
 * @param int|null $term Optional. The term ID for filtering events by the specific taxonomy's term.
 *                       Defaults to null, meaning no specific term filtering.
 *
 * @return string The URL to the next page of the list view.
 */
function tribe_get_listview_next_link( $term = null ) {
	$link = tribe_get_listview_dir_link( 'next', $term );
	return apply_filters( 'tribe_get_listview_next_link', $link, $term );
}

/**
 * Retrieves the link to a single event.
 *
 * This function returns the URL to a single event post, or if the `$full_link` parameter is set to true,
 * it outputs a complete HTML `<a>` tag with the event title as the link text. If no event is found, it returns false.
 *
 * @since 2.0.1
 *
 * @param WP_Post|int|null $post_id   Optional. The event post ID or WP_Post object. Defaults to the current post if not provided.
 * @param bool             $full_link Optional. If true, outputs a complete HTML `<a>` tag. Defaults to false, returning just the URL.
 *
 * @return string|false The URL to the event or an HTML `<a>` tag if `$full_link` is true. Returns false if no link is found.
 */
function tribe_get_event_link( $post_id = null, $full_link = false ) {
	$post_id = Tribe__Main::post_id_helper( $post_id );
	$url = Tribe__Events__Main::instance()->getLink( 'single', $post_id );

	if ( '' != get_option( 'permalink_structure' ) ) {
		$url = trailingslashit( $url );
	}

	if ( $full_link ) {
		$title_args = [ 'post' => $post_id, 'echo' => false ];
		$name       = get_the_title( $post_id );
		$attr_title = the_title_attribute( $title_args );
		$link       = false;

		if ( ! empty( $url ) && ! empty( $name ) ) {
			$link = sprintf(
				'<a href="%1$s" title="%2$s"">%3$s</a>',
				esc_url( $url ),
				$attr_title,
				$name
			);
		}
	} else {
		$link = $url;
	}

	/**
	 * Filters the permalink to events.
	 *
	 * @param string|false $link      The event link, either as an HTML `<a>` tag, URL, or false if not found.
	 * @param int          $post_id   The post ID of the event.
	 * @param bool         $full_link Whether to output a full HTML `<a>` link.
	 * @param string       $url       The URL of the event.
	 */
	return apply_filters( 'tribe_get_event_link', $link, $post_id, $full_link, $url );
}

/**
 * Retrieves the website link for an event, including an optional label and target attribute.
 *
 * This function returns the URL associated with the event and outputs it as an HTML anchor (`<a>`) element.
 * It also allows customization of the link label and target through filters.
 *
 * @since 3.0
 *
 * @param null|object|int $event  The event object or ID. Defaults to null - which will use global `$post`.
 * @param null|string     $label  Optional. The text label for the link. Defaults to the event URL if not provided.
 * @param string          $target Optional. The target attribute for the link. Defaults to '_self'. Allowed values are: '_self', '_blank', '_parent', '_top', '_unfencedTop'.
 *
 * @return string The HTML for the event website link, or an empty string if no URL is found.
 */
function tribe_get_event_website_link( $event = null, $label = null, $target = '_self' ): string {
	// We won't get far without a post ID. Especially since we pass it to filters that depend on it.
	$post_id = Tribe__Events__Main::postIdHelper( $event );
	$url     = tribe_get_event_website_url( $post_id );
	$target  = $target ? $target : '_self';

	/**
	 * Filters the target attribute for the event website link.
	 *
	 * This filter allows developers to modify the target attribute of the event website link.
	 *
	 * @since 5.1.0
	 * @since 5.5.0 Added $post_id argument.
	 *
	 * @param string          $target  The target attribute. Defaults to '_self'.
	 * @param string          $url     The event website URL.
	 * @param null|object|int $post_id The event the URL is attached to.
	 */
	$target = apply_filters( 'tribe_get_event_website_link_target', $target, $url, $post_id );

	// Ensure the target is given a valid value.
	$allowed = [ '_self', '_blank', '_parent', '_top', '_unfencedTop' ];
	if ( ! in_array( $target, $allowed ) ) {
		$target = '_self';
	}

	$rel    = ( '_blank' === $target ) ? 'noopener noreferrer' : 'external';

	if ( ! empty( $url ) ) {
		$label = empty( $label ) ? $url : $label;
		/**
		 * Filters the label of the event website link.
		 *
		 * This filter allows developers to modify the text label of the event website link.
		 *
		 * @since 3.0
		 *
		 * @param string $label   The link label. Defaults to the URL if not provided.
		 * @param int    $post_id The post ID of the event.
		 */
		$label = apply_filters( 'tribe_get_event_website_link_label', $label, $post_id );
		$html  = sprintf(
			'<a href="%s" target="%s" rel="%s">%s</a>',
			esc_url( $url ),
			esc_attr( $target ),
			esc_attr( $rel ),
			esc_html( $label )
		);
	} else {
		$html = '';
	}

	/**
	 * Filters the HTML output for the event website link.
	 *
	 * This filter allows developers to modify the full HTML string for the event website link.
	 *
	 * @since 3.0
	 *
	 * @param string $html The HTML for the event website link.
	 */
	return apply_filters( 'tribe_get_event_website_link', $html );
}

/**
 * Retrieves the formatted title for the event website link.
 *
 * This function fetches the event website title, which can be customized
 * using the 'tribe_events_get_event_website_title' filter.
 *
 * @since 5.5.0
 *
 * @param null|int $post_id Optional. The event post object or event ID. Defaults to null.
 *
 * @return string The formatted title for the event website link.
 */
function tribe_events_get_event_website_title( $post_id = null ) {
	$post_id = Tribe__Main::post_id_helper( $post_id );

	/**
	 * Filters the formatted title for the event's website link.
	 *
	 * This filter allows the customization of the text displayed as the
	 * event's website link title, such as changing or translating the string.
	 *
	 * @since 5.5.0
	 *
	 * @param string $title   The title for the event's website link. Default is 'Website:'.
	 * @param int    $post_id The event post ID.
	 */
	return apply_filters( 'tribe_events_get_event_website_title', __( 'Website:', 'the-events-calendar' ), $post_id );
}

/**
 * Event Website URL
 *
 * @param null|object|int $event
 *
 * @return string The event's website URL
 */
function tribe_get_event_website_url( $event = null ) {
	$post_id = ( is_object( $event ) && isset( $event->tribe_is_event ) && $event->tribe_is_event ) ? $event->ID : $event;
	$post_id = ( ! empty( $post_id ) || empty( $GLOBALS['post'] ) ) ? $post_id : get_the_ID();
	$url     = tribe_get_event_meta( $post_id, '_EventURL', true );

	return apply_filters( 'tribe_get_event_website_url', $url, $post_id );
}
