<?php
/**
 * Viewing settings tab.
 * Subtab of the General Tab.
 *
 * @since TBD
 */

$tec = Tribe__Events__Main::instance();

// Add the "Viewing" section.
$tec_events_general_viewing_fields = [
	'tec-events-settings-general-viewing-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-viewing">' . esc_html_x( 'Viewing', 'Title for the viewing section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	'unpretty-permalinks-url'                   => [
		'type'        => 'wrapped_html',
		'label'       => esc_html__( 'Events URL slug', 'the-events-calendar' ),
		'html'        => '<p>'
						. sprintf(
								/* Translators: %1$s - link to the front-end calendar page, %2$s - URL to the permalinks admin page. */
							__( 'The current URL for your events page is %1$s. <br><br> You cannot edit the slug for your events page as you do not have pretty permalinks enabled. In order to edit the slug here, <a href="%2$s">enable pretty permalinks</a>.', 'the-events-calendar' ),
							sprintf(
								'<a href="%1$s">%2$s</a>',
								esc_url( $tec->getLink( 'home' ) ),
								esc_url( $tec->getLink( 'home' ) )
							),
							esc_url( trailingslashit( get_admin_url() ) . 'options-permalink.php' )
						)
						. '</p>',
		'conditional' => ( '' == get_option( 'permalink_structure' ) ),
	],
	'eventsSlug'                                => [
		'type'            => 'text',
		'label'           => esc_html__( 'Events URL slug', 'the-events-calendar' ),
		'default'         => 'events',
		'validation_type' => 'slug',
		'conditional'     => ( '' !== get_option( 'permalink_structure' ) ),
	],
	'current-events-slug'                       => [
		'type'        => 'html',
		'html'        => '<p class="tribe-field-indent tribe-field-description description">'
							. esc_html__( 'The slug used for building the events URL.', 'the-events-calendar' )
							. ' '
							. sprintf(
								/* Translators: %1$s - URL to the events page (link), %2$s - URL to the events page (readable string) */
								esc_html__( 'Your current events URL is: <code><a href="%1$s">%2$s</a></code>', 'the-events-calendar' ),
								esc_url( tribe_get_events_link() ),
								urldecode( tribe_get_events_link() )
							)
							. '</p>',
		'conditional' => ( '' !== get_option( 'permalink_structure' ) ),
	],
	'singleEventSlug'                           => [
		'type'            => 'text',
		'label'           => esc_html__( 'Single event URL slug', 'the-events-calendar' ),
		'default'         => 'event',
		'validation_type' => 'slug',
		'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
	],
	'current-single-event-slug'                 => [
		'type'        => 'html',
		'html'        => '<p class="tribe-field-indent tribe-field-description description">'
				. sprintf(
					/* Translators: %1$s - URL to the single event page (readable string) */
					__( 'The above should ideally be plural, and this singular.<br />Your single event URL is: <code>%1$s</code>', 'the-events-calendar' ),
					trailingslashit( home_url() ) . urldecode( tribe_get_option( 'singleEventSlug', 'event' ) ) . '/single-post-name/'
				) . '</p>',
		'conditional' => ( '' != get_option( 'permalink_structure' ) ),
	],
	'showEventsInMainLoop'                      => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Include events in main blog loop', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Show events with the site\'s other posts. When this box is checked, events will also continue to appear on the default events page.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'enable_month_view_cache'                   => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Enable the Month View Cache', 'the-events-calendar' ),
		'tooltip'         => sprintf(
			/* Translators: %s - link to the Month View Cache documentation */
			__( 'Check this to cache your month view HTML in transients, which can help improve calendar speed on sites with many events. <a href="%s" rel="noopener" target="_blank">Read more</a>.', 'the-events-calendar' ),
			'https://evnt.is/18di'
		),
		'default'         => true,
		'validation_type' => 'boolean',
	],
];

$tec_events_general_viewing = new Tribe__Settings_Tab(
	'viewing',
	esc_html__( 'Viewing', 'the-events-calendar' ),
	[
		'priority' => 1,
		'fields'   => apply_filters( 'tribe_general_settings_viewing_section', $tec_events_general_viewing_fields ),
		'parent'   => 'general',
	]
);
