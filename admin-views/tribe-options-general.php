<?php

$tec = TribeEvents::instance();

$generalTab = array(
	'priority' => 10,
	'fields'   => apply_filters(
		'tribe_general_settings_tab_fields', array(
			'info-start'                    => array(
				'type' => 'html',
				'html' => '<div id="modern-tribe-info"><img src="' . plugins_url( 'resources/images/modern-tribe@2x.png', dirname( __FILE__ ) ) . '" alt="Modern Tribe Inc." title="Modern Tribe Inc.">'
			),
			'upsell-heading'                => array(
				'type'        => 'heading',
				'label'       => __( 'Finding & extending your calendar.', 'tribe-events-calendar' ),
				'conditional' => ( ! defined( 'TRIBE_HIDE_UPSELL' ) || ! TRIBE_HIDE_UPSELL ),
			),
			'finding-heading'               => array(
				'type'        => 'heading',
				'label'       => __( 'Finding your calendar.', 'tribe-events-calendar' ),
				'conditional' => ( defined( 'TRIBE_HIDE_UPSELL' ) && TRIBE_HIDE_UPSELL ),
			),
			'view-calendar-link'            => array(
				'type' => 'html',
				'html' => '<p>' . __( 'Where\'s my calendar?', 'tribe-events-calendar' ) . ' <a href="' . esc_url( TribeEvents::getLink() ) . '">' . __( 'Right here', 'tribe-events-calendar' ) . '</a>.</p>',
			),
			'upsell-info'                   => array(
				'type'        => 'html',
				'html'        => '<p>' . __( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ) . ' <a href="' . self::$tribeUrl . 'products/?utm_source=generaltab&utm_medium=plugin-tec&utm_campaign=in-app' . '">' . __( 'Check out the available add-ons', 'tribe-events-calendar' ) . '</a>.</p>',
				'conditional' => ( ! defined( 'TRIBE_HIDE_UPSELL' ) || ! TRIBE_HIDE_UPSELL ),
			),
			'donate-link-heading'           => array(
				'type'  => 'heading',
				'label' => __( 'We hope our plugin is helping you out.', 'tribe-events-calendar' ),
			),
			'donate-link-info'              => array(
				'type'        => 'html',
				'html'        => '<p>' . __( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to Modern Tribe for all their hard work." The greatest thanks we could ask for is recognition. Add a small text-only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-events-calendar' ) . '<br><a href="' . esc_url( plugins_url( 'resources/images/donate-link-screenshot.jpg', dirname( __FILE__ ) ) ) . '" class="thickbox">' . __( 'See an example of the link', 'tribe-events-calendar' ) . '</a>.</p>',
				'conditional' => ! class_exists( 'TribeEventsPro' ),
			),
			'donate-link-pro-info'          => array(
				'type'        => 'html',
				'html'        => '<p>' . __( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to Modern Tribe for all their hard work." The greatest thanks we could ask for is recognition. Add a small text only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-events-calendar' ) . '<br><a href="' . esc_url( plugins_url( 'resources/images/donate-link-pro-screenshot.jpg', dirname( __FILE__ ) ) ) . '" class="thickbox">' . __( 'See an example of the link', 'tribe-events-calendar' ) . '</a>.</p>',
				'conditional' => class_exists( 'TribeEventsPro' ),
			),
			'donate-link'                   => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Show The Events Calendar link', 'tribe-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'info-end'                      => array(
				'type' => 'html',
				'html' => '</div>',
			),
			'tribe-form-content-start'      => array(
				'type' => 'html',
				'html' => '<div class="tribe-settings-form-wrap">',
			),
			'tribeEventsDisplayThemeTitle'  => array(
				'type' => 'html',
				'html' => '<h3>' . __( 'General Settings', 'tribe-events-calendar' ) . '</h3>',
			),
			'postsPerPage'                  => array(
				'type'            => 'text',
				'label'           => __( 'Number of events to show per page', 'tribe-events-calendar' ),
				'size'            => 'small',
				'default'         => get_option( 'posts_per_page' ),
				'validation_type' => 'positive_int',
			),
			'liveFiltersUpdate'             => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Use Javascript to control date filtering', 'tribe-events-calendar' ),
				'tooltip'         => tribe_get_option( 'tribeDisableTribeBar', false ) == true ? __( 'This option is disabled when "Disable the Event Search Bar" is checked on the Display settings tab.', 'tribe-events-calendar' ) : __( 'Enable live ajax for datepicker on front end (User submit not required).', 'tribe-events-calendar' ),
				'attributes'      => tribe_get_option( 'tribeDisableTribeBar', false ) == true ? array( 'disabled' => 'disabled' ) : null,
				'default'         => true,
				'validation_type' => 'boolean',
				'class'           => tribe_get_option( 'tribeDisableTribeBar', false ) == true ? 'tribe-fieldset-disabled' : null,
			),
			'showComments'                  => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Show comments', 'tribe-events-calendar' ),
				'tooltip'         => __( 'Enable comments on event pages.', 'tribe-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'showEventsInMainLoop'          => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Include events in main blog loop', 'tribe-events-calendar' ),
				'tooltip'         => __( 'Show events with the site\'s other posts. When this box is checked, events will also continue to appear on the default events page.', 'tribe-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'unprettyPermalinksUrl'         => array(
				'type'        => 'html',
				'label'       => __( 'Events URL slug', 'tribe-events-calendar' ),
				'html'        => '<p>' . sprintf( __( 'You cannot edit the slug for your events page as you do not have pretty permalinks enabled. The current URL for your events page is <a href="%s">%s</a>. In order to edit the slug here, <a href="%soptions-permalink.php">enable pretty permalinks</a>.', 'tribe-events-calendar' ), esc_url( $tec->getLink( 'home' ) ), $tec->getLink( 'home ' ), esc_url( trailingslashit( get_admin_url() ) ) ) . '</p>',
				'conditional' => ( '' == get_option( 'permalink_structure' ) ),
			),
			'eventsSlug'                    => array(
				'type'            => 'text',
				'label'           => __( 'Events URL slug', 'tribe-events-calendar' ),
				'default'         => 'events',
				'validation_type' => 'slug',
				'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
			),
			'current-events-slug'           => array(
				'type'        => 'html',
				'html'        => '<p class="tribe-field-indent tribe-field-description description">' . __( 'The slug used for building the events URL.', 'tribe-events-calendar' ) . sprintf( __( 'Your current events URL is: %s', 'tribe-events-calendar' ), '<code><a href="' . esc_url( tribe_get_events_link() ) . '">' . tribe_get_events_link() . '</a></code>' ) . '</p>',
				'conditional' => ( '' != get_option( 'permalink_structure' ) ),
			),
			'ical-info'                     => array(
				'type'             => 'html',
				'display_callback' => ( function_exists( 'tribe_get_ical_link' ) ) ? '<p id="ical-link" class="tribe-field-indent tribe-field-description description">' . __( 'Here is the iCal feed URL for your events:', 'tribe-events-calendar' ) . ' ' . '<code>' . tribe_get_ical_link() . '</code></p>' : '',
				'conditional'      => function_exists( 'tribe_get_ical_link' ),
			),
			'singleEventSlug'               => array(
				'type'            => 'text',
				'label'           => __( 'Single event URL slug', 'tribe-events-calendar' ),
				'default'         => 'event',
				'validation_type' => 'slug',
				'conditional'     => ( '' != get_option( 'permalink_structure' ) ),
			),
			'current-single-event-slug'     => array(
				'type'        => 'html',
				'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( 'The above should ideally be plural, and this singular.<br />Your single event URL is: %s', 'tribe-events-calendar' ), '<code>' . trailingslashit( home_url() ) . tribe_get_option( 'singleEventSlug', 'event' ) . '/single-post-name/' . '</code>' ) . '</p>',
				'conditional' => ( '' != get_option( 'permalink_structure' ) ),
			),
			'multiDayCutoff'                => array(
				'type'            => 'dropdown',
				'label'           => __( 'End of day cutoff', 'tribe-events-calendar' ),
				'validation_type' => 'options',
				'size'            => 'small',
				'default'         => '12:00',
				'options'         => array(
					'00:00' => '12:00 am',
					'00:30' => '12:30 am',
					'01:00' => '01:00 am',
					'01:30' => '01:30 am',
					'02:00' => '02:00 am',
					'02:30' => '02:30 am',
					'03:00' => '03:00 am',
					'03:30' => '03:30 am',
					'04:00' => '04:00 am',
					'04:30' => '04:30 am',
					'05:00' => '05:00 am',
					'05:30' => '05:30 am',
					'06:00' => '06:00 am',
					'06:30' => '06:30 am',
					'07:00' => '07:00 am',
					'07:30' => '07:30 am',
					'08:00' => '08:00 am',
					'08:30' => '08:30 am',
					'09:00' => '09:00 am',
					'09:30' => '09:30 am',
					'10:00' => '10:00 am',
					'10:30' => '10:30 am',
					'11:00' => '11:00 am',
					'11:30' => '11:30 am'
				),
			),
			'multiDayCutoffHelper'          => array(
				'type'        => 'html',
				'html'        => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( "Have an event that runs past midnight? Select a time after that event's end to avoid showing the event on the next day's calendar.", 'tribe-events-calendar' ) ) . '</p>',
				'conditional' => ( '' != get_option( 'permalink_structure' ) ),
			),
			'defaultCurrencySymbol'         => array(
				'type'            => 'text',
				'label'           => __( 'Default currency symbol', 'tribe-events-calendar' ),
				'tooltip'         => __( 'Set the default currency symbol for event costs. Note that this only impacts future events, and changes made will not apply retroactively.', 'tribe-events-calendar' ),
				'validation_type' => 'textarea',
				'size'            => 'small',
				'default'         => '$',
			),
			'reverseCurrencyPosition'       => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Currency symbol follows value', 'tribe-events-calendar' ),
				'tooltip'         => __( 'The currency symbol normally precedes the value. Enabling this option positions the symbol after the value.', 'tribe-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'tribeEventsDisplayTitle'       => array(
				'type' => 'html',
				'html' => '<h3>' . __( 'Map Settings', 'tribe-events-calendar' ) . '</h3>',
			),
			'embedGoogleMaps'               => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Enable Google Maps', 'tribe-events-calendar' ),
				'tooltip'         => __( 'Check to enable maps for events and venues.', 'tribe-events-calendar' ),
				'default'         => true,
				'class'           => 'google-embed-size',
				'validation_type' => 'boolean',
			),
			'embedGoogleMapsZoom'           => array(
				'type'            => 'text',
				'label'           => __( 'Google Maps default zoom level', 'tribe-events-calendar' ),
				'tooltip'         => __( '0 = zoomed out; 21 = zoomed in.', 'tribe-events-calendar' ),
				'size'            => 'small',
				'default'         => 10,
				'class'           => 'google-embed-field',
				'validation_type' => 'number_or_percent',
			),
			'tribeEventsMiscellaneousTitle' => array(
				'type' => 'html',
				'html' => '<h3>' . __( 'Miscellaneous Settings', 'tribe-events-calendar' ) . '</h3>',
			),
			'amalgamateDuplicates'          => array(
				'type'        => 'html',
				'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . __( 'Duplicate Venues &amp; Organizers', 'tribe-events-calendar' ) . '</legend><div class="tribe-field-wrap">' . Tribe_Amalgamator::migration_button( __( 'Merge Duplicates', 'tribe-events-calendar' ) ) . '<p class="tribe-field-indent description">' . __( 'You might find duplicate venues and organizers when updating The Events Calendar from a pre-3.0 version. Click this button to automatically merge identical venues and organizers.', 'tribe-events-calendar' ) . '</p></div></fieldset><div class="clear"></div>',
				'conditional' => ( $tec->getOption( 'organizer_venue_amalgamation', 0 ) < 1 ),
			),
			'debugEvents'                   => array(
				'type'            => 'checkbox_bool',
				'label'           => __( 'Debug mode', 'tribe-events-calendar' ),
				'default'         => false,
				'validation_type' => 'boolean',
			),
			'debugEventsHelper'             => array(
				'type'        => 'html',
				'html'        => '<p class="tribe-field-indent tribe-field-description description" style="max-width:400px;">' . sprintf( __( 'Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the %s and look for the "Tribe" tab in the debug output.', 'tribe-events-calendar' ), '<a href="http://wordpress.org/extend/plugins/debug-bar/" target="_blank">' . __( 'Debug Bar Plugin', 'tribe-events-calendar' ) . '</a>' ) . '</p>',
				'conditional' => ( '' != get_option( 'permalink_structure' ) ),
			),
			'viewWelcomePage'          => array(
				'type'        => 'html',
				'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . __( 'View Welcome Page', 'tribe-events-calendar' ) . '</legend><div class="tribe-field-wrap"><a href="' . esc_url( get_site_url() . '/wp-admin/edit.php?post_type=tribe_events&page=tribe-events-calendar&tec-welcome-message' ) . '" class="button">' . __( 'View Welcome Page', 'tribe-events-calendar' ) . '</a><p class="tribe-field-indent description">' . __( 'View the page that displayed when you initially installed the plugin.', 'tribe-events-calendar' ) . '</p></div></fieldset><div class="clear"></div>',

			),
			'viewUpdatePage'          => array(
				'type'        => 'html',
				'html'        => '<fieldset class="tribe-field tribe-field-html"><legend>' . __( 'View Update Page', 'tribe-events-calendar' ) . '</legend><div class="tribe-field-wrap"><a href="' . esc_url( get_site_url() . '/wp-admin/edit.php?post_type=tribe_events&page=tribe-events-calendar&tec-update-message' ) . '" class="button">' . __( 'View Update Page', 'tribe-events-calendar' ) . '</a><p class="tribe-field-indent description">' . __( 'View the page that displayed when you updated the plugin.', 'tribe-events-calendar' ) . '</p></div></fieldset><div class="clear"></div>'
			),
			'tribe-form-content-end'        => array(
				'type' => 'html',
				'html' => '</div>',
			),
		)
	)
);

