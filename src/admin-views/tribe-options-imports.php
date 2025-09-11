<?php
/**
 * Handles the Import settings for The Events Calendar.
 *
 * @since 6.7.0
 */

declare( strict_types=1 );

use TEC\Common\Admin\Entities\Container;
use TEC\Common\Admin\Entities\Div;
use TEC\Common\Admin\Entities\Field_Wrapper;
use TEC\Common\Admin\Entities\Heading;
use TEC\Common\Admin\Entities\Link;
use TEC\Common\Admin\Entities\Paragraph;
use TEC\Common\Admin\Entities\Plain_Text;
use Tribe\Events\Admin\Settings as Plugin_Settings;
use Tribe\Utils\Element_Classes as Classes;

// Set up some variables we'll reuse in the options.
$internal                   = [];
$use_global_settings_phrase = esc_html__( 'Use global import settings', 'the-events-calendar' );
$post_statuses              = get_post_statuses();
$category_dropdown          = wp_dropdown_categories(
	[
		'echo'       => false,
		'hide_empty' => false,
		'orderby'    => 'post_title',
		'taxonomy'   => Tribe__Events__Main::TAXONOMY,
	]
);
preg_match_all( '!<option.*value="([^"]+)"[^>]*>(.*)</option>!m', $category_dropdown, $matches );
$categories                  = [
	'-1' => __( 'No default category', 'the-events-calendar' ),
];
$events_aggregator_is_active = tribe( 'events-aggregator.main' )->is_service_active();

$origin_post_statuses = $events_aggregator_is_active
	? [ '-1' => $use_global_settings_phrase ] + $post_statuses
	: $post_statuses;

$origin_categories = [
	'-1' => $events_aggregator_is_active
		? $use_global_settings_phrase
		: esc_html__(
			'None',
			'the-events-calendar'
		),
];

foreach ( $matches[1] as $key => $match ) {
	$categories[ $match ]        = $matches[2][ $key ];
	$origin_categories[ $match ] = $matches[2][ $key ];
}

$yes_no_options = [
	'no'  => __( 'No', 'the-events-calendar' ),
	'yes' => __( 'Yes', 'the-events-calendar' ),
];

$origin_show_map_options = [ '-1' => $use_global_settings_phrase ] + $yes_no_options;

/**
 * Filter to show all EA settings.
 *
 * @param bool $show_all_ea_settings Whether to show all EA settings.
 */
$show_all_ea_settings = (bool) apply_filters( 'tec_events_aggregator_show_all_settings', $events_aggregator_is_active );

/**
 * Helper function for wrapping fields.
 *
 * This will take the container and an array of fields, and the fields will all be
 * wrapped in a Field_Wrapper object and added to the container.
 *
 * @param Container $container The container to add the fields to.
 * @param array     $fields    Array of field data.
 *
 * @return void
 */
$wrap_fields = function ( Container $container, array $fields ) {
	foreach ( $fields as $field_id => $field ) {
		$container->add_child(
			new Field_Wrapper(
				new Tribe__Field(
					$field_id,
					$field
				)
			)
		);
	}
};

// Common elements.
$import_page            = new Container();
$section_header_classes = new Classes( [ 'tec-settings-form__section-header', 'tec-settings-form__section-header--sub' ] );
$empty_space            = new Plain_Text( ' ' );
$description_classes    = new Classes( [ 'tec-settings-form__section-description' ] );
$content_block          = new Div( new Classes( [ 'tec-settings-form__content-section' ] ) );

// Start the fields array.
$fields = [];

// Header section.
$header = new Div( new Classes( [ 'tec-settings-form__header-block', 'tec-settings-form__header-block--horizontal' ] ) );
$header->add_child(
	new Heading( __( 'Imports', 'the-events-calendar' ), 2, new Classes( [ 'tec-settings-form__section-header' ] ) )
);

// Add the correct header text based on whether the Events Aggregator is active.
if ( $events_aggregator_is_active ) {
	$header->add_child(
		( new Paragraph( $description_classes ) )->add_children(
			[
				new Plain_Text( __( 'Global Import Settings apply to all imports, but you can also override the global settings by adjusting the origin-specific options.', 'the-events-calendar' ) ),
				$empty_space,
				new Plain_Text( __( 'Check your Event Aggregator Service Status on the', 'the-events-calendar' ) ),
				$empty_space,
				new Link(
					tribe( 'tec.main' )->settings()->get_url( [ 'page' => 'tec-troubleshooting' ] ),
					__( 'Troubleshooting Page', 'the-events-calendar' )
				),
			]
		)
	);
} else {
	$header->add_children(
		[
			( new Paragraph( $description_classes ) )->add_child(
				new Plain_Text( __( 'Use the options below to configure your imports. Looking for more ways to import events from other websites?', 'the-events-calendar' ) ),
			),
			( new Paragraph( $description_classes ) )->add_child(
				new Link(
					'https://evnt.is/196z',
					__( 'Check out Event Aggregator', 'the-events-calendar' )
				)
			),
		]
	);
}

$fields[] = $header;

// Event Update Authority.
$event_update_authority = ( clone $content_block )->add_child(
	( new Div( new Classes( [ 'tec-settings-form__header-block' ] ) ) )->add_children(
		[
			new Heading( __( 'Event Update Authority', 'the-events-calendar' ), 3, $section_header_classes ),
			( new Paragraph( $description_classes ) )->add_child(
				new Plain_Text(
					__(
						'You can make changes to imported events via The Events Calendar and see those changes reflected on your site’s calendar.',
						'the-events-calendar'
					),
				)
			),
		]
	)
);

$wrap_fields(
	$event_update_authority,
	[
		'tribe_aggregator_default_update_authority' => [
			'type'            => 'radio',
			'label'           => esc_html__( 'Event Update Authority', 'the-events-calendar' ),
			'validation_type' => 'options',
			'default'         => Tribe__Events__Aggregator__Settings::$default_update_authority,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => [
				'overwrite'        => __( 'Overwrite my event with any changes from the original source.', 'the-events-calendar' ),
				'retain'           => __( 'Do not re-import events. Changes made locally will be preserved.', 'the-events-calendar' ),
				'preserve_changes' => __( 'Import events but preserve local changes to event fields.', 'the-events-calendar' ),
			],
			'priority'        => 1.3,
			'tooltip'         => __(
				' The owner of the original event source (e.g. the iCalendar feed or Meetup group) might also make changes to their event. If you choose to re-import an altered event (manually or via a scheduled import), any changes made at the source or on your calendar will need to be addressed.',
				'the-events-calendar'
			),
			'tooltip_first'   => true,
		],
	]
);

$fields[] = $event_update_authority;

// Set up the global import settings.
$global_import_settings = ( clone $content_block )->add_child(
	new Heading( __( 'Global Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

$wrap_fields(
	$global_import_settings,
	[
		'tribe_aggregator_default_post_status'         => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => 'publish',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $post_statuses,
			'priority'        => 5.2,
		],
		'tribe_aggregator_default_category'            => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $categories,
			'priority'        => 5.3,
		],
		'tribe_aggregator_default_show_map'            => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => 'no',
			'can_be_empty'    => false,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $yes_no_options,
			'priority'        => 5.4,
		],
		'tribe_aggregator_default_import_limit_type'   => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Import Limit Type', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Limit the number of imported events by number, date range, or not at all; on slower websites this may impact the success of imports. Selecting a shorter time period or a smaller number of events may improve results.', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => 'count',
			'can_be_empty'    => false,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => tribe( 'events-aggregator.settings' )->get_import_limit_type_options(),
			'priority'        => 5.5,
		],
		'tribe_aggregator_default_import_limit_range'  => [
			'type'                => 'dropdown',
			'label'               => esc_html__( 'Import Date Range Limit', 'the-events-calendar' ),
			'tooltip'             => esc_html__( 'When importing from an event source, this is how far into the future the events will be fetched; on slower websites a larger date range may impact the success of imports. Selecting a shorter time period may improve results.', 'the-events-calendar' ),
			'size'                => 'medium',
			'validation_type'     => 'options',
			'default'             => tribe( 'events-aggregator.settings' )->get_import_range_default( true ),
			'can_be_empty'        => true,
			'parent_option'       => Tribe__Events__Main::OPTIONNAME,
			'options'             => tribe( 'events-aggregator.settings' )->get_import_range_options( true ),
			'class'               => 'tribe-dependent',
			'fieldset_attributes' => [
				'data-depends'   => '#tribe_aggregator_default_import_limit_type-select',
				'data-condition' => 'range',
			],
			'priority'            => 5.6,
		],
		'tribe_aggregator_default_import_limit_number' => [
			'type'                => 'dropdown',
			'label'               => esc_html__( 'Import Quantity Limit', 'the-events-calendar' ),
			'tooltip'             => esc_html__( 'When importing from an event source, this is the maximum number of events that will be imported; on slower websites this may impact the success of imports. Setting this to a smaller number may improve results.', 'the-events-calendar' ),
			'size'                => 'medium',
			'validation_type'     => 'options',
			'default'             => tribe( 'events-aggregator.settings' )->get_import_limit_count_default(),
			'can_be_empty'        => true,
			'parent_option'       => Tribe__Events__Main::OPTIONNAME,
			'options'             => tribe( 'events-aggregator.settings' )->get_import_limit_count_options(),
			'class'               => 'tribe-dependent',
			'fieldset_attributes' => [
				'data-depends'   => '#tribe_aggregator_default_import_limit_type-select',
				'data-condition' => 'count',
			],
			'priority'            => 5.7,
		],
		'tribe_aggregator_import_process_system'       => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Import Process System', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The Asynchronous import process is faster and does not rely on WordPress Cron but might not work correctly in all WordPress installations, try switching to the Cron-based process for maximum compatibility.', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => tribe( 'events-aggregator.settings' )->get_import_process_default( false ),
			'can_be_empty'    => false,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => tribe( 'events-aggregator.settings' )->get_import_process_options( true ),
			'priority'        => 5.8,
		],
		'tribe_aggregator_import_process_control'      => [
			'type'     => 'wrapped_html',
			'label'    => esc_html__( 'Stop current processes', 'the-events-calendar' ),
			'html'     => sprintf(
				/* Translators: %1$s: link to stop current processes */
				__( 'If you want to stop and clear current asynchronous import processes %1$s.', 'the-events-calendar' ),
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( [ Tribe__Events__Aggregator__Processes__Queue_Control::CLEAR_PROCESSES => 1 ] ) ),
					esc_html__( 'click here', 'the-events-calendar' )
				)
			),
			'priority' => 5.9,
		],
	]
);

// Only add these items if the Events Aggregator is active.
if ( $show_all_ea_settings ) {
	$fields[] = $global_import_settings;
}

// Set up the CSV import settings.
$csv_import_settings = ( clone $content_block )->add_child(
	new Heading( __( 'CSV Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

$wrap_fields(
	$csv_import_settings,
	[
		'tribe_aggregator_default_csv_post_status' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via CSV', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => $events_aggregator_is_active ? '' : 'publish',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_post_statuses,
			'priority'        => 10.2,
		],
		'tribe_aggregator_default_csv_category'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via CSV', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 10.3,
		],
	]
);

$fields[] = $csv_import_settings;

// Set up Eventbrite Import settings.
$eventbrite_import_settings = ( clone $content_block )->add_child(
	new Heading( __( 'Eventbrite Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

// Ensure that "(do not override)" is set up for Eventbrite import statuses, and "Published" is not.
$eventbrite_origin_post_statuses = [ 'do_not_override' => esc_html__( '(do not override)', 'the-events-calendar' ) ];
$eventbrite_origin_post_statuses = $eventbrite_origin_post_statuses + $origin_post_statuses;

unset( $eventbrite_origin_post_statuses['publish'] );

// Unset EA's "Use global import settings" option if it's there.
if ( $events_aggregator_is_active ) {
	unset( $eventbrite_origin_post_statuses['-1'] );
}

$wrap_fields(
	$eventbrite_import_settings,
	[
		'tribe_aggregator_default_eventbrite_post_status' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via Eventbrite', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $eventbrite_origin_post_statuses,
			'priority'        => 17.2,
		],
		'tribe_aggregator_default_eventbrite_category'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via Eventbrite', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 17.3,
		],
		'tribe_aggregator_default_eventbrite_show_map'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => 'no',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_show_map_options,
			'priority'        => 17.4,
		],
	]
);

// Set up iCal settings.
$ical_import_settings = ( clone $content_block )->add_child(
	new Heading( __( 'iCalendar Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

$wrap_fields(
	$ical_import_settings,
	[
		'tribe_aggregator_default_ical_post_status' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via iCalendar', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_post_statuses,
			'priority'        => 20.2,
		],
		'tribe_aggregator_default_ical_category'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via iCalendar', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 20.3,
		],
		'tribe_aggregator_default_ical_show_map'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_show_map_options,
			'priority'        => 20.4,
		],
	]
);

// Set up ICS import settings.
$ics_import_settings = ( clone $content_block )->add_child(
	new Heading( __( 'ICS File Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

$wrap_fields(
	$ics_import_settings,
	[
		'tribe_aggregator_default_ics_post_status' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via .ics files', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_post_statuses,
			'priority'        => 25.2,
		],
		'tribe_aggregator_default_ics_category'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via .ics files', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 25.3,
		],
		'tribe_aggregator_default_ics_show_map'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_show_map_options,
			'priority'        => 25.4,
		],
	]
);

// Set up Google Calendar settings.
$google_import_settings = ( clone $content_block )->add_child(
	new Heading( __( 'Google Calendar Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

$wrap_fields(
	$google_import_settings,
	[
		'tribe_aggregator_default_gcal_post_status' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via Google Calendar', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_post_statuses,
			'priority'        => 35.2,
		],
		'tribe_aggregator_default_gcal_category'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via Google Calendar', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 35.3,
		],
		'tribe_aggregator_default_gcal_show_map'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_show_map_options,
			'priority'        => 35.4,
		],
	]
);

// Setup Meetup import settings.
$meetup_import_settings = ( clone $content_block )->add_child(
	( new Div( new Classes( [ 'tec-settings-form__header-block' ] ) ) )->add_children(
		[
			new Heading( __( 'Meetup Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
			( new Paragraph() )->add_children(
				[
					new Plain_Text(
						__( 'To import Meetup events, please be sure to add your Meetup API key here:', 'the-events-calendar' )
					),
					$empty_space,
					new Link(
						tribe( Plugin_Settings::class )->get_url( [ 'tab' => 'addons' ] ),
						__( 'Events > Settings > Integrations', 'the-events-calendar' )
					),
				]
			),
		]
	)
);

$wrap_fields(
	$meetup_import_settings,
	[
		'tribe_aggregator_default_meetup_post_status' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via Meetup', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_post_statuses,
			'priority'        => 40.3,
		],
		'tribe_aggregator_default_meetup_category'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via Meetup', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 40.4,
		],
		'tribe_aggregator_default_meetup_show_map'    => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_show_map_options,
			'priority'        => 40.5,
		],
	]
);

// Set up other URL settings.
$other_url_settings = ( clone $content_block )->add_child(
	new Heading( __( 'Other URL Import Settings', 'the-events-calendar' ), 3, $section_header_classes ),
);

$wrap_fields(
	$other_url_settings,
	[
		'tribe_aggregator_default_url_post_status'  => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default post status for events imported via other URLs', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_post_statuses,
			'priority'        => 45.2,
		],
		'tribe_aggregator_default_url_category'     => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'The default event category for events imported via other URLs', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_categories,
			'priority'        => 45.3,
		],
		'tribe_aggregator_default_url_show_map'     => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Show Map', 'the-events-calendar' ),
			'tooltip'         => esc_html__( 'Show map by default on imported event and venues', 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => '',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $origin_show_map_options,
			'priority'        => 45.4,
		],
		'tribe_aggregator_default_url_import_range' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Import Date Range Limit', 'the-events-calendar' ),
			'tooltip'         => esc_html__(
				'When importing from a website that uses The Events Calendar, the REST API will attempt to fetch events this far in the future. That website\'s hosting resources may impact the success of imports. Selecting a shorter time period may improve results.',
				'the-events-calendar'
			) . ' ' . sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_attr( 'https://theeventscalendar.com/knowledgebase/url-import-errors-event-aggregator/' ), esc_html__( 'Learn more.', 'the-events-calendar' ) ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => tribe( 'events-aggregator.settings' )->get_import_range_default(),
			'can_be_empty'    => false,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => tribe( 'events-aggregator.settings' )->get_url_import_range_options( true ),
			'priority'        => 45.5,
		],
		'tribe_aggregator_default_url_import_event' => [
			'type'            => 'dropdown',
			'label'           => esc_html__( 'Import Event Settings', 'the-events-calendar' ),
			'tooltip'         => esc_html__( "Fetch source event's settings (e.g. Show Maps Link or Sticky in Month View) when importing from another site using The Events Calendar.", 'the-events-calendar' ),
			'size'            => 'medium',
			'validation_type' => 'options',
			'default'         => 'no',
			'can_be_empty'    => true,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'options'         => $yes_no_options,
			'priority'        => 45.6,
		],
	]
);

// Set up the Event Aggregator control.
$event_aggregator_control = ( clone $content_block )->add_child(
	new Heading( __( 'Event Aggregator Control', 'the-events-calendar' ), 3, $section_header_classes )
);

$wrap_fields(
	$event_aggregator_control,
	[
		'tribe_aggregator_disable' => [
			'type'            => 'checkbox_bool',
			'label'           => __( 'Disable Event Aggregator imports', 'the-events-calendar' ),
			'tooltip'         => __( 'Stop all Event Aggregator imports from running. Existing imported events will not be affected. Imports via CSV file will still be available.', 'the-events-calendar' ),
			'default'         => false,
			'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			'validation_type' => 'boolean',
			'priority'        => 50.2,
		],
	]
);

// Add the sections if EA is active.
if ( $show_all_ea_settings ) {
	$fields[] = $eventbrite_import_settings;
	$fields[] = $ical_import_settings;
	$fields[] = $ics_import_settings;
	$fields[] = $google_import_settings;
	$fields[] = $meetup_import_settings;
	$fields[] = $other_url_settings;
	$fields[] = $event_aggregator_control;
}

$imports_tab = new Tribe__Settings_Tab(
	'imports',
	esc_html__( 'Imports', 'the-events-calendar' ),
	[
		'priority' => 50,
		/**
		 * Filter the fields for the imports settings tab.
		 *
		 * @since 6.7.0
		 *
		 * @param array $fields The fields for the imports settings tab.
		 */
		'fields'   => apply_filters( 'tec_events_settings_tab_imports_fields', $fields ),
	]
);

/**
 * Fires after the imports settings tab has been created.
 *
 * @since 6.7.0
 *
 * @param Tribe__Settings_Tab $imports_tab The imports settings tab.
 */
do_action( 'tec_events_settings_tab_imports', $imports_tab );

return $imports_tab;
