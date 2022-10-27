<?php

$tec              = Tribe__Events__Main::instance();
$site_time_format = get_option( 'time_format' );

$general_tab_fields = [
	'info-start'                    => [
		'type' => 'html',
		'html' => '<div id="modern-tribe-info">
					<img
						src="' . plugins_url( 'resources/images/logos/tec-brand.svg', dirname( __FILE__ ) ) . '"
						alt="' . esc_attr( 'The Events Calendar brand logo', 'the-event-calendar' ) . '"
					/>',
	],
	'upsell-info'                   => [
		'type'        => 'html',
		'html'        => '<p>' . esc_html__( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'the-event-calendar' ) . ' <a target="_blank" rel="noopener noreferrer" href="' . Tribe__Main::$tec_url . 'products/?utm_source=generaltab&utm_medium=plugin-tec&utm_campaign=in-app">' . esc_html__( 'Check out the available add-ons', 'the-event-calendar' ) . '</a>.</p>',
		'conditional' => ( ! tec_should_hide_upsell() ) && class_exists( 'Tribe__Events__Main' ),
	],
	'donate-link-heading'           => [
		'type'  => 'heading',
		'label' => esc_html__( 'We hope our plugin is helping you out.', 'the-event-calendar' ),
		'conditional' => class_exists( 'Tribe__Events__Main' ),
	],
	'donate-link-info'              => [
		'type'        => 'html',
		'html'        => '<p>' . esc_html__( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to The Events Calendar for all their hard work." The greatest thanks we could ask for is recognition. Add a small text-only link at the bottom of your calendar pointing to The Events Calendar project.', 'the-event-calendar' ) . '<br><a href="' . esc_url( plugins_url( 'resources/images/donate-link-screenshot.png', dirname( __FILE__ ) ) ) . '" class="thickbox">' . esc_html__( 'See an example of the link', 'the-event-calendar' ) . '</a>.</p>',
		'conditional' => class_exists( 'Tribe__Events__Main' ),
	],
	'donate-link'                   => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show The Events Calendar link', 'the-event-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
		'conditional' => class_exists( 'Tribe__Events__Main' ),
	],
	'info-end'                      => [
		'type' => 'html',
		'html' => '</div>',
	],
	'tribe-form-content-start'      => [
		'type' => 'html',
		'html' => '<div class="tribe-settings-form-wrap">',
	],
	'tribe-form-content-end'        => [
		'type' => 'html',
		'html' => '</div>',
	],
];

$tec_events_general_heading_text = tec_should_hide_upsell()
	? esc_html__( 'Finding your calendar.', 'the-events-calendar' )
	: esc_html__( 'Finding & extending your calendar.', 'the-events-calendar' );

$tec_events_general_header = [
	// after info-start
	'upsell-heading'     => [
		'type'  => 'heading',
		'label' => $tec_events_general_heading_text,
	],
];

$general_tab_fields = Tribe__Main::array_insert_after_key(
	'info-start',
	$general_tab_fields,
	$tec_events_general_header
);

$tec_events_general_editing = [
	'tec-events-settings-general-editing-title' => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Editing', 'the-events-calendar' ) . '</h3>',
	],
	'disable_metabox_custom_fields'    => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Show Custom Fields metabox', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Enable WordPress Custom Fields on events in the classic editor.', 'the-events-calendar' ),
		'default'         => true,
		'validation_type' => 'boolean',
	],
	'showEventsInMainLoop'             => [
		'type'            => 'checkbox_bool',
		'label'           => esc_html__( 'Include events in main blog loop', 'the-events-calendar' ),
		'tooltip'         => esc_html__( 'Show events with the site\'s other posts. When this box is checked, events will also continue to appear on the default events page.', 'the-events-calendar' ),
		'default'         => false,
		'validation_type' => 'boolean',
	],
	'unpretty-permalinks-url'          => [
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
	'eventsSlug'                       => [
		'type'            => 'text',
		'label'           => esc_html__( 'Events URL slug', 'the-events-calendar' ),
		'default'         => 'events',
		'validation_type' => 'slug',
		'conditional'     => ( '' !== get_option( 'permalink_structure' ) ),
	],
	'current-events-slug'              => [
		'type'        => 'html',
		'html'        => '<p class="tribe-field-indent tribe-field-description description">' . esc_html__( 'The slug used for building the events URL.', 'the-events-calendar' ) . ' ' . sprintf( esc_html__( 'Your current events URL is: %s', 'the-events-calendar' ), '<code><a href="' . esc_url( tribe_get_events_link() ) . '">' . urldecode( tribe_get_events_link() ) . '</a></code>' ) . '</p>',
		'conditional' => ( '' !== get_option( 'permalink_structure' ) ),
	],
	'singleEventSlug'                  => [
		'type'            => 'text',
		'label'           => esc_html__( 'Single event URL slug', 'the-events-calendar' ),
		'default'         => 'event',
		'validation_type' => 'slug',
		'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
	],
	'current-single-event-slug'        => [
		'type'        => 'html',
		'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( 'The above should ideally be plural, and this singular.<br />Your single event URL is: %s', 'the-events-calendar' ), '<code>' . trailingslashit( home_url() ) . urldecode( tribe_get_option( 'singleEventSlug', 'event' ) ) . '/single-post-name/</code>' ) . '</p>',
		'conditional' => ( '' != get_option( 'permalink_structure' ) ),
	],
];

$tec_events_general_data = [
	'tec-events-settings-general-data-title' => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Data', 'the-events-calendar' ) . '</h3>',
	],
	'amalgamate-duplicates'                         => [
		'type'        => 'html',
		'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . esc_html__( 'Duplicate Venues &amp; Organizers', 'the-events-calendar' ) . '</legend><div class="tribe-field-wrap">' . Tribe__Events__Amalgamator::migration_button( esc_html__( 'Merge Duplicates', 'the-events-calendar' ) ) . '<p class="tribe-field-indent description">' . esc_html__( 'Click this button to automatically merge identical venues and organizers.', 'the-events-calendar' ) . '</p></div></fieldset>',
	],
	tribe( 'tec.event-cleaner' )->key_trash_events  => [
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
	tribe( 'tec.event-cleaner' )->key_delete_events => [
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
];

$tec_events_general_troubleshooting = [
	'tec-events-settings-general-troubleshooting-title' => [
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Troubleshooting', 'the-events-calendar' ) . '</h3>',
	],
	'view-calendar-link' => [
		'type' => 'html',
		'html' => '<fieldset class="tribe-field tribe-field-html"><legend>'
			. esc_html__( 'Where\'s my calendar?', 'the-events-calendar' )
			. '</legend><div class="tribe-field-wrap"><a href="'
			. esc_url( tribe( 'tec.main' )->getLink() ) . '">'
			. esc_html__( 'Right here', 'the-events-calendar' )
			. '</a>.</div></fieldset><div class="clear"></div>',
	],
	'ical-info'                        => [
		'type'             => 'html',
		'display_callback' => '<p id="ical-link" class="tribe-field-indent tribe-field-description description">' . esc_html__( 'Here is the iCal feed URL for your events:', 'the-events-calendar' ) . ' <code>' . tribe_get_ical_link() . '</code></p>',
		'conditional'      => function_exists( 'tribe_get_ical_link' ), // @TODO: this never loads.
	],
	'view-welcome-page'                  => [
		'type'        => 'html',
		'html'        =>
			'<fieldset class="tribe-field tribe-field-html"><legend>' .
				esc_html__( 'View Welcome Page', 'the-events-calendar' ) .
			'</legend><div class="tribe-field-wrap"><a href="' . tribe( 'tec.main' )->settings()->get_url( [ Tribe__Events__Main::instance()->activation_page->welcome_slug => 1 ] ) . '" class="button">' . esc_html__( 'View Welcome Page', 'the-events-calendar' ) . '</a><p class="tribe-field-indent description">' . esc_html__( 'View the page that displayed when you initially installed the plugin.', 'the-events-calendar' ) . '</p></div></fieldset>',
	],
	'debugEvents' => [
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

$tec_events_general_settings = $tec_events_general_editing + $tec_events_general_data + $tec_events_general_troubleshooting;

$general_tab_fields = Tribe__Main::array_insert_after_key(
	'tribe-form-content-start',
	$general_tab_fields,
	$tec_events_general_settings
);

$general_tab_fields = tribe( 'events.editor.compatibility' )->insert_toggle_blocks_editor_field( $general_tab_fields );

$general_tab_fields = apply_filters( 'tribe-event-general-settings-fields', $general_tab_fields );

$general_tab = [
	'priority' => 0,
	'fields'   => apply_filters( 'tribe_general_settings_tab_fields', $general_tab_fields ),
];
