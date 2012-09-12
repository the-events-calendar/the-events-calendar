<?php
$displayPressTrendsDialogue = tribe_get_option( 'displayedPressTrendsDialogue', false );

$displayPressTrendsDialogueValue = ( $displayPressTrendsDialogue == false ) ? '1' : '0';

if ( $displayPressTrendsDialogue == false ) {
	tribe_update_option( 'displayedPressTrendsDialogue', true );
}

$generalTab = array(
	'priority' => 10,
	'fields' => apply_filters( 'tribe_general_settings_tab_fields', array(
		'info-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info"><img src="' . plugins_url( 'resources/images/modern-tribe.png', dirname( __FILE__ ) ) . '" alt="Modern Tribe Inc." title="Modern Tribe Inc.">'
		),		
		'upsell-heading' => array(
			'type' => 'heading',
			'label' => __( 'Finding & extending your calendar.', 'tribe-events-calendar' ),
			'conditional' => ( !defined( 'TRIBE_HIDE_UPSELL' ) || !TRIBE_HIDE_UPSELL ),
		),
		'finding-heading' => array(
			'type' => 'heading',
			'label' => __( 'Finding your calendar.', 'tribe-events-calendar' ),
			'conditional' => ( defined( 'TRIBE_HIDE_UPSELL' ) && TRIBE_HIDE_UPSELL ),
		),
		'view-calendar-link' => array(
			'type' => 'html',
			'html' => '<p>' . __('Where\'s my calendar?', 'tribe-events-calendar') . '<br /><a href="' . TribeEvents::getLink() . '">' . __( 'Right here', 'tribe-events-calendar' ) . '</a>.</p>',
		),
		'upsell-info' => array(
			'type' => 'html',
			'html' => '<p>' . __( 'Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ) . '<br><a href="' . self::$tribeUrl . 'shop/?utm_source=generaltab&utm_medium=promolink&utm_campaign=plugin'.'">' . __( 'Check out the available Add-Ons', 'tribe-events-calendar' ) . '</a>.</p>',
			'conditional' => ( !defined( 'TRIBE_HIDE_UPSELL' ) || !TRIBE_HIDE_UPSELL ),
		),
		'donate-link-heading' => array(
			'type' => 'heading',
			'label' => __( 'We hope our plugin is helping you out.', 'tribe-events-calendar' ),
		),
		'donate-link-info' => array(
			'type' => 'html',
			'html' => '<p>' . __( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to Modern Tribe for all their hard work." The greatest thanks we could ask for is recognition. Add a small text only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-events-calendar' ).'<br><a href="' . plugins_url( 'resources/images/donate-link-screenshot.jpg', dirname( __FILE__ ) ).'" class="thickbox">' . __( 'See an example of the link', 'tribe-events-calendar' ) . '</a>.</p>',
			'conditional' => !class_exists( 'TribeEventsPro' ),
		),
		'donate-link-pro-info' => array(
			'type' => 'html',
			'html' => '<p>' . __( 'Are you thinking "Wow, this plugin is amazing! I should say thanks to Modern Tribe for all their hard work." The greatest thanks we could ask for is recognition. Add a small text only link at the bottom of your calendar pointing to The Events Calendar project.', 'tribe-events-calendar' ) . '<br><a href="' . plugins_url( 'resources/images/donate-link-pro-screenshot.jpg', dirname( __FILE__ ) ) . '" class="thickbox">' . __( 'See an example of the link', 'tribe-events-calendar' ) . '</a>.</p>',
			'conditional' => class_exists( 'TribeEventsPro' ),
		),
		'donate-link' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Show Events Calendar Link', 'tribe-events-calendar' ),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'info-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'viewOption' => array(
			'type' => 'radio',
			'label' => __( 'Default view for the Events', 'tribe-events-calendar' ),
			'default' => 'month',
			'options' => array( 'month' => 'Calendar', 'upcoming' => 'Event List' ),
			'validation_type' => 'options',
		),
		'unprettyPermalinksUrl' => array(
			'type' => 'html',
			'label' => __( 'Events URL slug', 'tribe-events-calendar' ),
			'html' => '<p class="tribe-field-indent tribe-field-description description">' . __( 'You cannot edit the slug for your events page as you do not have pretty permalinks enabled. The current URL for your events page is <a href=" ' . TribeEvents::getLink( 'home' ) . '">' . TribeEvents::getLink( 'home ' ) . '</a>. In order to edit the slug here, enable pretty permalinks.', 'tribe-events-calendar' ) . '</p>',
			'conditional' => ('' == get_option( 'permalink_structure' ) ),
		),
		'eventsSlug' => array(
			'type' => 'text',
			'label' => __( 'Events URL slug', 'tribe-events-calendar' ),
			'default' => 'events',
			'validation_type' => 'slug',
			'conditional' => ( '' != get_option( 'permalink_structure' ) ),
		 ),
		'current-events-slug' => array(
			'type' => 'html',
			'html' => '<p class="tribe-field-indent tribe-field-description description">' . __( 'The slug used for building the events URL.', 'tribe-events-calendar' ) . sprintf( __( 'Your current Events URL is %s', 'tribe-events-calendar' ), '<code><a href="'.tribe_get_events_link() . '">' . tribe_get_events_link() . '</a></code>' ) . '</p>',
			'conditional' => ( '' != get_option( 'permalink_structure' ) ),
		),
		'ical-info' => array(
			'type' => 'html',
			'display_callback' => ( function_exists( 'tribe_get_ical_link' ) ) ? '<p id="ical-link" class="tribe-field-indent tribe-field-description description">' . __( 'Here is the iCal feed URL for your events:', 'tribe-events-calendar' ) . ' ' . '<code>' . tribe_get_ical_link() . '</code></p>' : '',
			'conditional' => function_exists( 'tribe_get_ical_link' ),
		),
		'singleEventSlug' => array(
			'type' => 'text',
			'label' => __( 'Single Event URL slug', 'tribe-events-calendar' ),
			'default' => 'event',
			'validation_type' => 'slug',
			'conditional' => ( '' != get_option( 'permalink_structure' ) ),
		 ),
		'current-single-event-slug' => array(
			'type' => 'html',
			'html' => '<p class="tribe-field-indent tribe-field-description description">' . sprintf( __( 'You <strong>cannot</strong> use the same slug as above. The above should ideally be plural, and this singular.<br />Your single Event URL is like: %s', 'tribe-events-calendar' ), '<code>' . trailingslashit( home_url() ) . tribe_get_option( 'singleEventSlug', 'event' ) . '/single-post-name/' . '</code>' ) . '</p>',
			'conditional' => ( '' != get_option( 'permalink_structure' ) ),
		),
		'postsPerPage' => array(
			'type' => 'text',
			'label' => __( 'Number of events to show per page', 'tribe-events-calendar' ),
			'size' => 'small',
			'default' => get_option( 'posts_per_page' ),
			'validation_type' => 'positive_int',
		 ),
		'showComments' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Show Comments', 'tribe-events-calendar' ),
			'tooltip' => __( 'Enable commenting on an event.', 'tribe-events-calendar' ),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'multiDayCutoff' => array(
			'type' => 'dropdown',
		 	'label' => __( 'Multiday Event Cutoff', 'tribe-events-calendar' ),
			'tooltip' => __( 'Hide final day from grid view if multi-day event ends before this time.', 'tribe-events-calendar' ),
			'validation_type' => 'options',
			'size' => 'small',
			'default' => '12:00',
			'options' => array( '12:00' => '12:00 am', '12:30' => '12:30 am', '01:00' => '01:00 am', '01:30' => '01:30 am', '02:00' => '02:00 am', '02:30' => '02:30 am', '03:00' => '03:00 am', '03:30' => '03:30 am', '04:00' => '04:00 am', '04:30' => '04:30 am', '05:00' => '05:00 am', '05:30' => '05:30 am', '06:00' => '06:00 am', '06:30' => '06:30 am', '07:00' => '07:00 am', '07:30' => '07:30 am', '08:00' => '08:00 am', '08:30' => '08:30 am', '09:00' => '09:00 am', '09:30' => '09:30 am', '10:00' => '10:00 am', '10:30' => '10:30 am', '11:00' => '11:00 am', '11:30' => '11:30 am' ),
		),
		'embedGoogleMaps' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Enable Google Maps', 'tribe-events-calendar' ),
			'tooltip' => __( 'Turn on to enable backend map preview and frontend map.', 'tribe-events-calendar' ),
			'default' => true,
			'class' => 'google-embed-size',
			'validation_type' => 'boolean',
		),
		'embedGoogleMapsHeight' => array(
			'type' => 'text',
			'label' => __( 'Google Maps Embed Height', 'tribe-events-calendar' ),
			'size' => 'small',
			'default' => 350,
			'tooltip' => __( 'Enter a number.', 'tribe-events-calendar' ),
			'class' => 'google-embed-field',
			'validation_type' => 'positive_int',
		 ),
		'embedGoogleMapsWidth' => array(
			'type' => 'text',
			'label' => __( 'Google Maps Embed Width', 'tribe-events-calendar' ),
			'size' => 'small',
			'tooltip' => __( 'Enter a number or %.', 'tribe-events-calendar' ),
			'default' => '100%',
			'class' => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		 ),
		'embedGoogleMapsZoom' => array(
			'type' => 'text',
			'label' => __( 'Google Maps Default Zoom Level', 'tribe-events-calendar' ),
			'tooltip' => __( '0 = zoomed out; 21 = zoomed in.', 'tribe_events_calendar' ),
			'size' => 'small',
			'default' => 10,
			'class' => 'google-embed-field',
			'validation_type' => 'number_or_percent',
		 ),
		'sendPressTrendsData' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Send PressTrends Data', 'tribe-events-calendar' ),
			'tooltip' => __( 'Help us out by sending analytics data about your usage of The Events Calendar.', 'tribe-events-calendar' ),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'debugEvents' => array(
			'type' => 'checkbox_bool',
			'label' => __( 'Debug Mode', 'tribe-events-calendar' ),
			'tooltip' => sprintf( __( 'Enable this option to log debug information. By default this will log to your server PHP error log. If you\'d like to see the log messages in your browser, then we recommend that you install the %s and look for the "Tribe" tab in the debug output.', 'tribe-events-calendar' ), '<a href="http://wordpress.org/extend/plugins/debug-bar/" target="_blank">' . __( 'Debug Bar Plugin', 'tribe-events-calendar' ).'</a>' ),
			'default' => false,
			'validation_type' => 'boolean',
		),
		'maybeDisplayPressTrendsDialogue' => array(
			'type' => 'html',
			'html' => '<input type="hidden" name="maybeDisplayPressTrendsDialogue" value="' . $displayPressTrendsDialogueValue . '"></input>',
		),
		'pressTrendsDialogue' => array(
			'type' => 'html',
			'html' => '<div id="presstrends-dialog" title="Send PressTrends Data" style="display: none;">' . __('Would you like to help us out and send analytics about your usage of The Events Calendar?','tribe-events-calendar') .'<br/></div>',
		),
	)
) 
);