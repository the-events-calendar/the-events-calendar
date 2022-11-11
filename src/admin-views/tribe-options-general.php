<?php

$tec              = Tribe__Events__Main::instance();
$site_time_format = get_option( 'time_format' );

$tec_events_general_heading_text = tec_should_hide_upsell()
	? esc_html__( 'Finding your calendar.', 'the-events-calendar' )
	: esc_html__( 'Finding & extending your calendar.', 'the-events-calendar' );

$event_cleaner = tribe( 'tec.event-cleaner' );

$general_tab_fields = [
	'info-start'             => [
		'type' => 'html',
		'html' => '<div id="modern-tribe-info" class="tec-settings-header">',
	],
	'upsell-heading'          => [
		'type'  => 'heading',
		'label' => $tec_events_general_heading_text,
	],
	'view-calendar-link'     => [
		'type' => 'html',
		'html' => '<p>'
			. esc_html__( 'Where\'s my calendar?', 'the-events-calendar' )
			. '&nbsp;<a href="'
			. esc_url( tribe( 'tec.main' )->getLink() ) . '">'
			. esc_html__( 'Right here', 'the-events-calendar' )
			. '</a>.</p>',
	],
	'tec-setup-guide'        => [
		'type' => 'html',
		'html' => '<p>'. esc_html__( 'Learn how to setup The Events Calendar and find advanced functionality and customization with our welcome guide.', 'the-events-calendar' ) .'</p>'
	],
	'tec-setup-guide-button' => [
		'type' => 'html',
		'html' => sprintf(
			'<a href="%1$s" class="button">%2$s</a>',
			'https://theeventscalendar.com/knowledgebase/guide/the-events-calendar/',
			_x( 'Getting Started Guide', 'Text for button-style link to Getting Started guide.', 'the-events-calendar')
		),
	],
	'donate-link-heading'           => [
		'type'  => 'heading',
		'label' => esc_html__( 'We hope our plugin is helping you out.', 'tribe-common' ),
	],
	'donate-link-info'              => [
		'type'        => 'html',
		'html'        => '<p>' . esc_html__( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to The Events Calendar for all their hard work." The greatest thanks we could ask for is recognition. Add a small text-only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-common' ) . '<br><a href="' . esc_url( plugins_url( 'resources/images/donate-link-screenshot.png', dirname( __FILE__ ) ) ) . '" class="thickbox">' . esc_html__( 'See an example of the link', 'tribe-common' ) . '</a>.</p>',
	],
	'donate-link'                               => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show The Events Calendar link', 'the-event-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'ical-info'              => [
		'type'             => 'html',
		'display_callback' => '<p id="ical-link" class="tribe-field-indent tribe-field-description description">' . esc_html__( 'Here is the iCal feed URL for your events:', 'the-events-calendar' ) . ' <code>' . tribe_get_ical_link() . '</code></p>',
		'conditional'      => function_exists( 'tribe_get_ical_link' ), // @TODO: this never loads.
	],
	'info-end'                 => [
		'type' => 'html',
		'html' => '</div>',
	],
];

// Add the TOC
$tec_events_general_toc = [
	'tec-settings-general-toc-nav-start'             => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header">',
	],
	'tec-settings-general-toc-start'           => [
		'type' => 'html',
		'html' => '<ul id="tec-events-general-toc" class="tec-events-settings__toc">',
	],
	'tec-settings-general-toc-label'           => [
		'type' => 'html',
		'html' => '<li>' . _x( 'Jump to:', 'Text introducing the table of contents links.', 'the-events-calendar' ) . '</li>',
	],
	'tec-settings-general-toc-viewing'         => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-general-viewing">' . _x( 'Viewing', 'Viewing table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-settings-general-toc-editing'         => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-general-editing">' . _x( 'Editing', 'Editing table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-settings-general-toc-troubleshooting' => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-general-troubleshooting">' . _x( 'Troubleshooting', 'Troubleshooting table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-settings-general-toc-end'             => [
		'type' => 'html',
		'html' => '</ul>',
	],
	'tec-settings-general-toc-nav-end'                 => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$general_tab_fields += $tec_events_general_toc;

// Start the form content wrapper.
$tec_events_general_form_end = [

	'tribe-form-content-start' => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap">',
	],
];

$general_tab_fields += $tec_events_general_form_end;

// Add the "Viewing" section.
$tec_events_general_viewing = [
	'tec-events-settings-general-viewing-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-viewing">' . esc_html__( 'Viewing', 'the-events-calendar' ) . '</h3>',
	],
	'unpretty-permalinks-url'                   => [
		'type'  => 'wrapped_html',
		'label' => esc_html__( 'Events URL slug', 'the-events-calendar' ),
		'html'  => '<p>'
			. sprintf(
				__( 'The current URL for your events page is %1$s. <br><br> You cannot edit the slug for your events page as you do not have pretty permalinks enabled. In order to edit the slug here, <a href="%2$s">enable pretty permalinks</a>.', 'the-events-calendar' ),
				sprintf (
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
		'html'        => '<p class="tribe-field-indent tribe-field-description description">' . esc_html__( 'The slug used for building the events URL.', 'the-events-calendar' ) . ' ' . sprintf( esc_html__( 'Your current events URL is: %s', 'the-events-calendar' ), '<code><a href="' . esc_url( tribe_get_events_link() ) . '">' . urldecode( tribe_get_events_link() ) . '</a></code>' ) . '</p>',
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
		'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( 'The above should ideally be plural, and this singular.<br />Your single event URL is: %s', 'the-events-calendar' ), '<code>' . trailingslashit( home_url() ) . urldecode( tribe_get_option( 'singleEventSlug', 'event' ) ) . '/single-post-name/</code>' ) . '</p>',
		'conditional' => ( '' != get_option( 'permalink_structure' ) ),
	],
	'showEventsInMainLoop'                      => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Include events in main blog loop', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Show events with the site\'s other posts. When this box is checked, events will also continue to appear on the default events page.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	$event_cleaner->key_trash_events            => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Move to trash events older than', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'This option allows you to automatically move past events to trash.', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => null,
		'options'         => [
			null => esc_html__( 'Disabled', 'the-events-calendar' ),
			1    => esc_html__( '1 month', 'the-events-calendar' ),
			3    => esc_html__( '3 months', 'the-events-calendar' ),
			6    => esc_html__( '6 months', 'the-events-calendar' ),
			9    => esc_html__( '9 months', 'the-events-calendar' ),
			12   => esc_html__( '1 year', 'the-events-calendar' ),
			24   => esc_html__( '2 years', 'the-events-calendar' ),
			36   => esc_html__( '3 years', 'the-events-calendar' ),
		],
	],
	$event_cleaner->key_delete_events           => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Permanently delete events older than', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'This option allows you to bulk delete past events. Be careful and backup your database before removing your events as there is no way to reverse the changes.', 'the-events-calendar' ),
		'validation_type' => 'options',
		'size'            => 'small',
		'default'         => null,
		'options'         => [
			null => esc_html__( 'Disabled', 'the-events-calendar' ),
			1    => esc_html__( '1 month', 'the-events-calendar' ),
			3    => esc_html__( '3 months', 'the-events-calendar' ),
			6    => esc_html__( '6 months', 'the-events-calendar' ),
			9    => esc_html__( '9 months', 'the-events-calendar' ),
			12   => esc_html__( '1 year', 'the-events-calendar' ),
			24   => esc_html__( '2 years', 'the-events-calendar' ),
			36   => esc_html__( '3 years', 'the-events-calendar' ),
		],
	],
	'enable_month_view_cache'                   => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Enable the Month View Cache', 'the-events-calendar' ),
		'tooltip'         => sprintf( __( 'Check this to cache your month view HTML in transients, which can help improve calendar speed on sites with many events. <a href="%s">Read more</a>.', 'the-events-calendar' ), 'https://evnt.is/18di' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
];

$general_tab_fields += $tec_events_general_viewing;

$tec_events_general_aggregator_infobox = [
	'tec-aggregator-infobox-start' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">'
	],
	'tec-aggregator-infobox-logo' => [
		'type' => 'html',
		'html' => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'resources/images/settings-icons/icon-event-aggregator.svg', dirname( __FILE__ ) ) . '" alt="Events Aggregator Logo">',
	],
	'tec-aggregator-infobox-title' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' .  __( 'Import events with Event Aggregator', 'the-events-calendar' ) . '</h3>',
	],
	'tec-aggregator-infobox-content' => [
		'type' => 'html',
		'html' => '<p>' . __( 'Effortlessly fill your calendar with events from Meetup, Eventbrite, iCal, Google Calendar, and more.', 'the-events-calendar' ) . '</p>',
	],
	'tec-aggregator-infobox-end' => [
		'type' => 'html',
		'html' => '</div>'
	],
];

// Add the "Editing" section.
$tec_events_general_editing = [
	'tec-events-settings-general-editing-title'      => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-editing">' . esc_html__( 'Editing', 'the-events-calendar' ) . '</h3>',
	],
	'tec-aggregator-infobox-start' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">'
	],
	'tec-aggregator-infobox-logo' => [
		'type' => 'html',
		'html' => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'resources/images/settings-icons/icon-event-aggregator.svg', dirname( __FILE__ ) ) . '" alt="Events Aggregator Logo">',
	],
	'tec-aggregator-infobox-title' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' .  __( 'Import events with Event Aggregator', 'the-events-calendar' ) . '</h3>',
	],
	'tec-aggregator-infobox-content' => [
		'type' => 'html',
		'html' => '<p>' . __( 'Effortlessly fill your calendar with events from Meetup, Eventbrite, iCal, Google Calendar, and more.', 'the-events-calendar' ) . '</p>',
	],
	'tec-aggregator-infobox-link' => [
		'type' => 'html',
		'html' => '<a href="' . esc_url( 'https://evnt.is/1bby' ) . '">' . __( 'Learn more.', 'the-events-calendar' ) . '</a>',
	],
	'tec-aggregator-infobox-end' => [
		'type' => 'html',
		'html' => '</div>'
	],
	'disable_metabox_custom_fields'                  => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show Custom Fields metabox', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable WordPress Custom Fields on events in the classic editor.', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
	'amalgamate-duplicates'                          => [
		'type'        => 'html',
		'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . esc_html__( 'Merge duplicate Venues &amp; Organizers', 'the-events-calendar' ) . '</legend><div class="tribe-field-wrap">' . Tribe__Events__Amalgamator::migration_button( esc_html__( 'Merge Duplicates', 'the-events-calendar' ) ) . '<p class="tribe-field-indent description">' . esc_html__( 'Click this button to automatically merge identical venues and organizers.', 'the-events-calendar' ) . '</p></div></fieldset>',
	],
];

$general_tab_fields += $tec_events_general_editing;

// Add the "Troubleshooting" section.
$tec_events_general_troubleshooting = [
	'tec-events-settings-general-troubleshooting-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-troubleshooting">' . esc_html__( 'Troubleshooting', 'the-events-calendar' ) . '</h3>',
	],
	'view-welcome-page'                                 => [
		'type'        => 'html',
		'html'        =>
			'<fieldset class="tribe-field tribe-field-html"><legend>' .
				esc_html__( 'View Welcome Page', 'the-events-calendar' ) .
			'</legend>
			<div class="tribe-field-wrap">
			<a href="' . tribe( 'tec.main' )->settings()->get_url( [ $tec->activation_page->welcome_slug => 1 ] ) . '" class="button">' . esc_html__( 'View Welcome Page', 'the-events-calendar' ) . '</a><p class="tribe-field-indent description">' . esc_html__( 'View the page that displayed when you initially installed the plugin.', 'the-events-calendar' ) . '</a></div></fieldset>',
	],
	'debugEvents'                                       => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Debug mode', 'the-event-calendar' ),
		'tooltip' => sprintf(
			esc_html__(
				'Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the %s and look for the "Tribe" tab in the debug output.',
				'the-event-calendar'
			),
			'<a target="_blank" rel="noopener noreferrer" href="https://wordpress.org/extend/plugins/debug-bar/">' . esc_html__( 'Debug Bar Plugin', 'the-event-calendar' ) . '</a>'
		),
		'default'         => false,
		'validation_type' => 'boolean',
		'conditional'     => is_super_admin()
	],
];

$general_tab_fields += $tec_events_general_troubleshooting;

// Close the form content wrapper.
$general_tab_fields += [
	'tribe-form-content-end' => [
		'type' => 'html',
		'html' => '</div>',
	]
];

// Backwards compatibility.
$general_tab = apply_filters_deprecated( 'tribe_general_settings_tab_fields', [ $general_tab_fields ], 'TBD', 'tribe-event-general-settings-fields' );

$general_tab = [
	'priority' => 0,
	'fields'   => apply_filters( 'tribe-event-general-settings-fields', $general_tab_fields ),
];
