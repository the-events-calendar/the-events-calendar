<?php
/**
 * Create a easy way to hook to the Add-ons Tab Fields
 * @var array
 */
$internal = array();
$use_global_settings_phrase = __( 'Use global import settings', 'the-events-calendar' );
$post_statuses = get_post_statuses( array() );
$origin_post_statuses = array( '' => $use_global_settings_phrase ) + $post_statuses;
$category_dropdown = wp_dropdown_categories( array(
	'echo'       => false,
	'hide_empty' => false,
	'orderby'    => 'post_title',
	'taxonomy'   => Tribe__Events__Main::TAXONOMY,
) );
preg_match_all( '!\<option.*value="([^"]+)"[^\>]*\>(.*)\</option\>!m', $category_dropdown, $matches );
$categories = array(
	'' => __( 'No default category', 'the-events-calendar' ),
);

$origin_categories = array(
	'' => $use_global_settings_phrase,
);

foreach ( $matches[1] as $key => $match ) {
	$categories[ $match ]        = $matches[2][ $key ];
	$origin_categories[ $match ] = $matches[2][ $key ];
}

$show_map_options = array(
	'no' => __( 'No', 'the-events-calendar' ),
	'yes' => __( 'Yes', 'the-events-calendar' ),
);

$origin_show_map_options = array( '' => $use_global_settings_phrase ) + $show_map_options;

$change_authority = array(
	'import-defaults-update_authority' => array(
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'Event Update Authority', 'the-events-calendar' ) . '</h3>',
	),
	'info-update_authority' => array(
		'type' => 'html',
		'html' => '<p>' . esc_html__( 'You can make changes to imported events via The Events Calendar and see those changes reflected on your site’s calendar. The owner of the original event source (e.g. the iCalendar feed or Facebook group) might also make changes to their event. If you choose to re-import an altered event (manually or via a scheduled import), any changes made at the source or on your calendar will need to be addressed.', 'the-events-calendar' ) . '</p>',
	),
	'tribe_aggregator_default_update_authority' => array(
		'type' => 'radio',
		'label' => esc_html__( 'Event Update Authority', 'the-events-calendar' ),
		'validation_type' => 'options',
		'default' => 'retain',
		'parent_option' => Tribe__Events__Main::OPTIONNAME,
		'options' => array(
			'overwrite' => __( 'Overwrite my event with any changes from the original source.', 'the-events-calendar' ),
			'retain' => __( 'Do not re-import events. Changes made locally will be preserved.', 'the-events-calendar' ),
			'preserve_changes' => __( 'Preserve the most recent change in each event field, whether that change occurred on my site or at the original source.', 'the-events-calendar' ),
		),
	),
);

$csv = array(
	'csv-defaults' => array(
		'type' => 'html',
		'html' => '<h3>' . esc_html__( 'CSV Import Settings', 'the-events-calendar' ) . '</h3>',
	),
	'tribe_aggregator_default_csv_post_status' => array(
		'type' => 'dropdown',
		'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
		'tooltip' => esc_html__( 'The default post status for events imported via CSV', 'the-events-calendar' ),
		'size' => 'medium',
		'validation_type' => 'options',
		'default' => '',
		'can_be_empty' => true,
		'parent_option' => Tribe__Events__Main::OPTIONNAME,
		'options' => $origin_post_statuses,
	),
	'tribe_aggregator_default_csv_category' => array(
		'type' => 'dropdown',
		'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
		'tooltip' => esc_html__( 'The default event category for events imported via CSV', 'the-events-calendar' ),
		'size' => 'medium',
		'validation_type' => 'options',
		'default' => '',
		'can_be_empty' => true,
		'parent_option' => Tribe__Events__Main::OPTIONNAME,
		'options' => $origin_categories,
	),
);

$global = $ical = $facebook = $gcal = $meetup = array();
// if there's an Event Aggregator license key, add the Global settings, Facebook, iCal, and Meetup fields
if ( Tribe__Events__Aggregator::is_service_active() ) {
	$global = array(
		'import-defaults' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Global Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribe_aggregator_default_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'publish',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $post_statuses,
		),
		'tribe_aggregator_default_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $categories,
		),
		'tribe_aggregator_default_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $show_map_options,
		),
	);

	$ical = array(
		'ical-defaults' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'iCal and .ics File Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribe_aggregator_default_ical_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via iCal, Google Calendar, and .ics files', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_post_statuses,
		),
		'tribe_aggregator_default_ical_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events imported via iCal, Google Calendar, and .ics files', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_categories,
		),
		'tribe_aggregator_default_ical_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$facebook = array(
		'facebook-defaults' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Facebook Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'facebook-defaults-info' => array(
			'type' => 'html',
			'html' => '<p>' . sprintf(
				esc_html__(
					'To import Facebook events, please be sure to add your Facebook API key and secret on %1$sEvents > Settings > APIs%2$s',
					'the-events-calendar'
				),
				'<a href="' . admin_url( Tribe__Settings::$parent_page . '&page=tribe-common&tab=addons' ) . '">',
				'</a>'
			). '</p>',
		),
		'tribe_aggregator_default_facebook_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via Facebook', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_post_statuses,
		),
		'tribe_aggregator_default_facebook_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events imported via Facebook', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_categories,
		),
		'tribe_aggregator_default_facebook_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$gcal = array(
		'gcal-defaults' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Google Calendar Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribe_aggregator_default_gcal_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via Google Calendar', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_post_statuses,
		),
		'tribe_aggregator_default_gcal_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events imported via Google Calendar', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_categories,
		),
		'tribe_aggregator_default_gcal_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$meetup = array(
		'meetup-defaults' => array(
			'type' => 'html',
			'html' => '<h3>' . esc_html__( 'Meetup Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'meetup-defaults-info' => array(
			'type' => 'html',
			'html' => '<p>' . sprintf(
				esc_html__(
					'To import Meetup events, please be sure to add your Meetup API key on %1$sEvents > Settings > APIs%2$s',
					'the-events-calendar'
				),
				'<a href="' . admin_url( Tribe__Settings::$parent_page . '&page=tribe-common&tab=addons' ) . '">',
				'</a>'
			). '</p>',
		),
		'tribe_aggregator_default_meetup_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via Meetup', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_post_statuses,
		),
		'tribe_aggregator_default_meetup_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events imported via Meetup', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_categories,
		),
		'tribe_aggregator_default_meetup_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);
}

$internal = array_merge(
	$change_authority,
	$global,
	$csv,
	$ical,
	$facebook,
	$gcal,
	$meetup
);

$internal = apply_filters( 'tribe_aggregator_fields', $internal );

$fields = array_merge(
	array(
		'import-box-start' => array(
			'type' => 'html',
			'html' => '<div id="modern-tribe-info">',
		),
		'import-box-title' => array(
			'type' => 'html',
			'html' => '<h2>' . esc_html__( 'Imports', 'the-events-calendar' ) . '</h2>',
		),
		'import-box-description' => array(
			'type' => 'html',
			'html' => '<p>' . __( 'Please follow the instructions below to configure your settings.', 'the-events-calendar' ) . '</p>',
		),
		'import-box-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
		'import-form-content-start' => array(
			'type' => 'html',
			'html' => '<div class="tribe-settings-form-wrap">',
		),
	),
	$internal,
	array(
		'addons-form-content-end' => array(
			'type' => 'html',
			'html' => '</div>',
		),
	)
);

/**
 * Allow developer to fully filter the Addons Tab contents
 * Following the structure of the arguments for a Tribe__Settings_Tab instance
 *
 * @var array
 */
$import = apply_filters(
	'tribe_aggregator_tab',
	array(
		'priority' => 50,
		'fields'   => $fields,
	)
);

// Only create the Add-ons Tab if there is any
if ( ! empty( $internal ) ) {
	new Tribe__Settings_Tab( 'imports', esc_html__( 'Imports', 'the-events-calendar' ), $import );
}
