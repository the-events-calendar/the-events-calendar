<?php

$tec              = Tribe__Events__Main::instance();
$site_time_format = get_option( 'time_format' );

$tec_events_general_heading_text = tec_should_hide_upsell()
	? esc_html__( 'Finding your calendar.', 'the-events-calendar' )
	: esc_html__( 'Finding & extending your calendar.', 'the-events-calendar' );

/**
 * @var Tribe__Events__Event_Cleaner $event_cleaner
 */
$event_cleaner = tribe( 'tec.event-cleaner' );

$general_tab_fields = [
	'info-start'                                     => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header">',
	],
	'upsell-heading'                                 => [
		'type'  => 'heading',
		'label' => $tec_events_general_heading_text,
	],
	'tec-setup-guide'                                => [
		'type' => 'html',
		'html' => '<p>'. esc_html__( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'the-events-calendar' )
	],
	'tec-add-ons-link'                               => [
		'type' => 'html',
		'html' => sprintf(
			'<br><a href="%1$s">%2$s</a></p>',
			esc_url( 'edit.php?post_type=tribe_events&page=tribe-app-shop' ),
			esc_html_x( 'Check out the available add-ons.', 'Text for link to the add-ons page.', 'the-events-calendar')
		),
	],
	'tec-links-section-start'                        => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header-links-section">',
	],
	'tec-links-documentation-section-start'          => [
		'type' => 'html',
		'html' => '<ul class="tec-settings-header-links-section__documentation">'
				.'<li>' . esc_html__( 'Documentation', 'the-events-calendar' ) . '</li>',
	],
	'tec-documentation-section-welcome-page-link'    => [
		'type' => 'html',
		'html' => '<li><a href="'
			. esc_url( tribe( 'tec.main' )->settings()->get_url( [ Tribe__Events__Main::instance()->activation_page->welcome_slug => 1 ] ) ) . '">'
			. esc_html__( 'View Welcome Page', 'the-events-calendar' )
			. '</a></li>',
	],
	'tec-documentation-section-getting-started-link' => [
		'type' => 'html',
		'html' => '<li><a href="'
			. esc_url( 'https://evnt.is/1bbv' ) . '" rel="noopener" target="_blank">'
			. esc_html__( 'Getting started guide', 'the-events-calendar' )
			. '</a></li>',
	],
	'tec-documentation-section-knowledgebase-link'   => [
		'type' => 'html',
		'html' => '<li><a href="'
			. esc_url( 'https://evnt.is/1bbw' ) . '" rel="noopener" target="_blank">'
			. esc_html__( 'Knowledgebase', 'the-events-calendar' )
			. '</a></li>',
	],
	'tec-links-documentation-section-end'            => [
		'type' => 'html',
		'html' => '</ul>',
	],
	'tec-links-help-section-start'                   => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header-links-section__help">',
	],
	'tec-view-calendar-link'                         => [
		'type' => 'html',
		'html' => '<ul>'
			. '<li>' . esc_html__( 'Where\'s my calendar?', 'the-events-calendar' ). '</li>'
			.'<li><a href="'
			. esc_url( tribe( 'tec.main' )->getLink() ) . '">'
			. esc_html__( 'Right here', 'the-events-calendar' )
			. '</a></li>'

			. '</ul>',
	],
	'tec-having-trouble-links'                       => [
		'type' => 'html',
		'html' => '<ul>'
			. '<li>' . esc_html__( 'Having trouble?', 'the-events-calendar' ). '</li>'
			.'<li><a href="'
			. esc_url( 'edit.php?post_type=tribe_events&page=tec-events-help' ) . '">'
			. esc_html__( 'Help', 'the-events-calendar' )
			. '</a></li>'
			.'<li><a href="'
			. esc_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' ) . '">'
			. esc_html__( 'Troubleshoot', 'the-events-calendar' )
			. '</a></li>'
			. '</ul>',
	],
	'tec-links-help-section-end'                     => [
		'type' => 'html',
		'html' => '</div>',
	],
	'tec-links-donate-section-start'                 => [
		'type' => 'html',
		'html' => '<div class="tec-settings-header-links-section__donate">',
	],
	'tec-donate-link-info'                           => [
		'type'        => 'html',
		'html'        => '<p>' . esc_html__( 'If you’re enjoying The Events Calendar, give us kudos by including a link in the footer of calendar views. It really helps us a lot.', 'tribe-common' ) . '</p>',
	],
	'donate-link'                                    => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show The Events Calendar link', 'the-event-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'tec-links-donate-section-end'                   => [
		'type' => 'html',
		'html' => '</div>',
	],
	'tec-links-section-end'                          => [
		'type' => 'html',
		'html' => '</div>',
	],
	'ical-info'                                      => [
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
	'tec-settings-general-toc-nav-start'       => [
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
	'tec-settings-general-toc-maintenance'         => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-general-maintenance">' . _x( 'Maintenance', 'Maintenance table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-settings-general-toc-debugging' => [
		'type' => 'html',
		'html' => '<li><a href="#tec-settings-general-debugging">' . _x( 'Debugging', 'Debugging table of contents link.', 'the-events-calendar' ) . '</a>',
	],
	'tec-settings-general-toc-end'             => [
		'type' => 'html',
		'html' => '</ul>',
	],
	'tec-settings-general-toc-nav-end'         => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$general_tab_fields += $tec_events_general_toc;

// Start the form content wrapper.
$tec_events_general_form_end = [

	'tribe-form-content-start' => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap tec-settings-general">',
	],
];

$general_tab_fields += $tec_events_general_form_end;

// Add the "Viewing" section.
$tec_events_general_viewing = [
	'tec-events-settings-general-viewing-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-viewing">' . esc_html_x( 'Viewing', 'Title for the viewing section of the general settings.', 'the-events-calendar' ) . '</h3>',
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
	'enable_month_view_cache'                   => [
		'type'            => 'checkbox_bool',
		'label'           => __( 'Enable the Month View Cache', 'the-events-calendar' ),
		'tooltip'         => sprintf( __( 'Check this to cache your month view HTML in transients, which can help improve calendar speed on sites with many events. <a href="%s" rel="noopener" target="_blank">Read more</a>.', 'the-events-calendar' ), 'https://evnt.is/18di' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
];

$general_tab_fields += $tec_events_general_viewing;

$is_missing_aggregator_license_key = '' === get_option( 'pue_install_key_event_aggregator' );
$should_hide_upsell                = tec_should_hide_upsell();

// Add the "Editing" section.
$tec_events_general_editing = [
	'tec-events-settings-general-editing-title'      => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-editing">' . esc_html_x( 'Editing', 'Title for the editing section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	'tec-aggregator-infobox-start' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-logo' => [
		'type' => 'html',
		'html' => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'resources/images/settings-icons/icon-event-aggregator.svg', dirname( __FILE__ ) ) . '" alt="Events Aggregator Logo">',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-title' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' .  __( 'Import events with Event Aggregator', 'the-events-calendar' ) . '</h3>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-content' => [
		'type' => 'html',
		'html' => '<p>' . __( 'Effortlessly fill your calendar with events from Meetup, Eventbrite, iCal, Google Calendar, and more.', 'the-events-calendar' ) . '</p>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-link' => [
		'type' => 'html',
		'html' => '<a href="' . esc_url( 'https://evnt.is/1bby' ) . '" rel="noopener" target="_blank">' . __( 'Learn more.', 'the-events-calendar' ) . '</a>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'tec-aggregator-infobox-end' => [
		'type' => 'html',
		'html' => '</div>',
		'conditional' => $is_missing_aggregator_license_key && ! $should_hide_upsell,
	],
	'disable_metabox_custom_fields'                  => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show Custom Fields metabox', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable WordPress Custom Fields on events in the classic editor.', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
];

$general_tab_fields += $tec_events_general_editing;

// Our default tooltip.
$trash_tooltip = esc_html__( 'This option allows you to automatically move past events to trash.', 'the-events-calendar' );
// Some adjusted functionality with CT1 activated.
if ( tribe()->getVar( 'ct1_fully_activated' ) ) {
	$trash_tooltip = sprintf(
		__( 'Trashed events will permanently be deleted in %1$d days, you can change that value using <code>%2$s</code>. <a href="%3$s" rel="noopener noreferrer" target="_blank">Read more.</a>', 'the-events-calendar' ),
		(int) EMPTY_TRASH_DAYS,
		'EMPTY_TRASH_DAYS',
		'https://evnt.is/1bcs'
	);
}

// Add the "Maintenance" section.
$tec_events_general_maintenance = [
	'tec-events-settings-general-maintenance-title'      => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-maintenance">' . esc_html_x( 'Maintenance', 'Title for the maintenance section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	$event_cleaner->key_trash_events => [
		'type'            => 'dropdown',
		'label'           => esc_html__( 'Move to trash events older than', 'the-events-calendar' ),
		'tooltip'         => $trash_tooltip,
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
	'amalgamate-duplicates'                          => [
		'type'        => 'html',
		'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . esc_html__( 'Merge duplicate Venues &amp; Organizers', 'the-events-calendar' ) . '</legend><div class="tribe-field-wrap">' . Tribe__Events__Amalgamator::migration_button( esc_html__( 'Merge Duplicates', 'the-events-calendar' ) ) . '<p class="tribe-field-indent description">' . esc_html__( 'Click this button to automatically merge identical venues and organizers.', 'the-events-calendar' ) . '</p></div></fieldset>',
	],
];

$general_tab_fields += $tec_events_general_maintenance;

// Add the "Debugging" section.
$tec_events_general_debugging = [
	'tec-events-settings-general-debugging-title' => [
		'type' => 'html',
		'html' => '<h3 id="tec-settings-general-debugging">' . esc_html_x( 'Debugging', 'Title for the debugging section of the general settings.', 'the-events-calendar' ) . '</h3>',
	],
	'tec-troubleshooting-infobox-start' => [
		'type' => 'html',
		'html' => '<div class="tec-settings-infobox">'
	],
	'tec-troubleshooting-infobox-logo' => [
		'type' => 'html',
		'html' => '<img class="tec-settings-infobox-logo" src="' . plugins_url( 'resources/images/settings-icons/icon-image-high-five.svg', dirname( __FILE__ ) ) . '" alt="Events troubleshooting Logo">',
	],
	'tec-troubleshooting-infobox-title' => [
		'type' => 'html',
		'html' => '<h3 class="tec-settings-infobox-title">' .  __( 'There is a solution for every problem', 'the-events-calendar' ) . '</h3>',
	],
	'tec-troubleshooting-infobox-content' => [
		'type' => 'html',
		'html' => sprintf(
			/* Translators: %1$s - opening paragraph tag, %2$s - opening anchor tag, %3$s - closing anchor tag, %4$s - closing paragraph tag */
			 __( '%1$sSometimes things just don’t work as expected. The %2$stroubleshooting page%3$s has a wealth of resources to get you back on track.%4$s', 'the-events-calendar' ),
			 '<p>',
			 '<a href="' . esc_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' ) . '">',
			 '</a>',
			 '</p>',
		),

	],
	'tec-troubleshooting-infobox-end' => [
		'type' => 'html',
		'html' => '</div>',
	],
	'debugEvents'                                       => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Debug mode', 'the-event-calendar' ),
		'tooltip'         => sprintf(
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

$general_tab_fields += $tec_events_general_debugging;

// Close the form content wrapper.
$general_tab_fields += [
	'tribe-form-content-end' => [
		'type' => 'html',
		'html' => '</div>',
	]
];

$general_tab = [
	'priority' => 0,
	'fields'   => apply_filters( 'tribe_general_settings_tab_fields', $general_tab_fields ),
];
