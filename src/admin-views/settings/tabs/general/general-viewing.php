<?php
/**
 * Viewing settings tab.
 * Subtab of the General Tab.
 *
 * @since 6.7.0
 */

$tec = Tribe__Events__Main::instance();

// Add the "Viewing" section.
$tec_events_general_viewing_fields = [
	'tec-events-settings-general-viewing-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-viewing" class="tec-settings-form__section-header">' . esc_html_x( 'Viewing', 'Title for the viewing section of the general settings.', 'the-events-calendar' ) . '</h3>',
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
		'tooltip_first'   => true,
		'tooltip'         => sprintf(
			/* Translators: %1$s - URL to the events page (link), %2$s - URL to the events page (readable string) */
			wp_kses_post( __( 'Preview: <a href="%1$s">%2$s</a>', 'the-events-calendar' ) ),
			esc_url( tribe_get_events_link() ),
			urldecode( tribe_get_events_link() )
		),
		'validation_type' => 'slug',
		'conditional'     => ( '' !== get_option( 'permalink_structure' ) ),
		'append'          => '<p class="tribe-field-description description">' . esc_html__( 'The above should be plural.', 'the-events-calendar' ) . '</p>',
	],
	'singleEventSlug'                           => [
		'type'            => 'text',
		'label'           => esc_html__( 'Single event URL slug', 'the-events-calendar' ),
		'append'          => '<p class="tribe-field-description description">' . __( 'The above should be singular.', 'the-events-calendar' ) . '</p>',
		'default'         => 'event',
		'tooltip_first'   => true,
		'tooltip'         => sprintf(
			/* Translators: %1$s - URL to a single events page (link), %2$s - URL to a single events page (readable string) */
			wp_kses_post( __( 'Preview: <a href="%1$s">%2$s</a>', 'the-events-calendar' ) ),
			esc_url( home_url() . tribe_get_option( 'singleEventSlug', 'event' ) . '/single-post-name/' ),
			trailingslashit( home_url() ) . urldecode( tribe_get_option( 'singleEventSlug', 'event' ) ) . '/single-post-name/'
		),
		'validation_type' => 'slug',
		'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
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
	'donate-link'                               => [
		'type'            => 'checkbox_bool',
		'tooltip'         => esc_html__( 'Show The Events Calendar link', 'the-events-calendar' ),
		'label'           => esc_html__( 'If you’re enjoying The Events Calendar, give us kudos by including a link in the footer of calendar views. It really helps us a lot.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
];

$general_viewing = new Tribe__Settings_Tab(
	'general-viewing-tab',
	esc_html__( 'Viewing', 'the-events-calendar' ),
	[
		'priority' => 0.0,
		'fields'   => apply_filters( 'tec_general_settings_viewing_section', $tec_events_general_viewing_fields ),
	]
);

/**
 * Fires after the Viewing settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $viewing The Viewing settings tab.
 */
do_action( 'tec_events_settings_tab_general_viewing', $general_viewing );

return $general_viewing;
