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

$yes_no_options = array(
	'no' => __( 'No', 'the-events-calendar' ),
	'yes' => __( 'Yes', 'the-events-calendar' ),
);

$origin_show_map_options = array( '' => $use_global_settings_phrase ) + $yes_no_options;

$change_authority = array(
	'import-defaults-update_authority' => array(
		'type' => 'html',
		'html' => '<h3 id="tribe-import-update-authority">' . esc_html__( 'Event Update Authority', 'the-events-calendar' ) . '</h3>',
	),
	'info-update_authority' => array(
		'type' => 'html',
		'html' => '<p>' . esc_html__( 'You can make changes to imported events via The Events Calendar and see those changes reflected on your site’s calendar. The owner of the original event source (e.g. the iCalendar feed or Facebook group) might also make changes to their event. If you choose to re-import an altered event (manually or via a scheduled import), any changes made at the source or on your calendar will need to be addressed.', 'the-events-calendar' ) . '</p>',
	),
	'tribe_aggregator_default_update_authority' => array(
		'type' => 'radio',
		'label' => esc_html__( 'Event Update Authority', 'the-events-calendar' ),
		'validation_type' => 'options',
		'default' => Tribe__Events__Aggregator__Settings::$default_update_authority,
		'parent_option' => Tribe__Events__Main::OPTIONNAME,
		'options' => array(
			'overwrite' => __( 'Overwrite my event with any changes from the original source.', 'the-events-calendar' ),
			'retain' => __( 'Do not re-import events. Changes made locally will be preserved.', 'the-events-calendar' ),
			'preserve_changes' => __( 'Import events but preserve local changes to event fields.', 'the-events-calendar' ),
		),
	),
);

$csv = array(
	'csv-defaults' => array(
		'type' => 'html',
		'html' => '<h3 id="tribe-import-csv-settings">' . esc_html__( 'CSV Import Settings', 'the-events-calendar' ) . '</h3>',
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

$ea_disable = array(
	'tribe_aggregator_disable_header' => array(
		'type' => 'html',
		'html' => '<h3 id="tribe-import-ea-disable">' . esc_html__( 'Event Aggregator Control', 'the-events-calendar' ) . '</h3>',
	),
	'tribe_aggregator_disable'               => array(
		'type'            => 'checkbox_bool',
		'label'           => __( 'Disable Event Aggregator imports', 'the-events-calendar' ),
		'tooltip'         => __( 'Stop all Event Aggregator imports from running. Existing imported events will not be affected. Imports via CSV file will still be available.', 'the-events-calendar' ),
		'default'         => false,
		'parent_option' => Tribe__Events__Main::OPTIONNAME,
		'validation_type' => 'boolean',
	),
);

$global = $ical = $ics = $facebook = $gcal = $meetup = $url = array();
// if there's an Event Aggregator license key, add the Global settings, Facebook, iCal, and Meetup fields
if ( Tribe__Events__Aggregator::is_service_active() ) {
	$global = array(
		'import-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-global-settings">' . esc_html__( 'Global Import Settings', 'the-events-calendar' ) . '</h3>',
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
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $yes_no_options,
		),
		'tribe_aggregator_default_import_limit_type' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Import limit type', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Limit the number of imported events by number, date range, or not at all; on slower websites this may impact the success of imports. Selecting a shorter time period or a smaller number of events may improve results.', 'the-events-calendar' ),

			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'range',
			'can_be_empty' => false,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => tribe( 'events-aggregator.settings' )->get_import_limit_type_options(),
		),
		'tribe_aggregator_default_import_limit_range' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Import date range limit', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'When importing from an event source, this is how far into the future the events will be fetched; on slower websites a larger date range may impact the success of imports. Selecting a shorter time period may improve results.', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => tribe( 'events-aggregator.settings' )->get_import_range_default( true ),
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => tribe( 'events-aggregator.settings' )->get_import_range_options( true ),
			'class' => 'tribe-dependent',
			'fieldset_attributes' => array(
				'data-depends'   => '#tribe_aggregator_default_import_limit_type-select',
				'data-condition' => 'range',
			),
		),
		'tribe_aggregator_default_import_limit_number' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Import quantity limit', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'When importing from an event source, this is the maximum number of events that will be imported; on slower websites this may impact the success of imports. Setting this to a smaller number may improve results.', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => tribe( 'events-aggregator.settings' )->get_import_limit_count_default(),
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => tribe( 'events-aggregator.settings' )->get_import_limit_count_options(),
			'class' => 'tribe-dependent',
			'fieldset_attributes' => array(
				'data-depends'   => '#tribe_aggregator_default_import_limit_type-select',
				'data-condition' => 'count',
			),
		),
	);

	$ical = array(
		'ical-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-ical-settings">' . esc_html__( 'iCalendar Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribe_aggregator_default_ical_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via iCalendar', 'the-events-calendar' ),
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
			'tooltip' => esc_html__( 'The default event category for events imported via iCalendar', 'the-events-calendar' ),
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
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$ics = array(
		'ics-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-ics-settings">' . esc_html__( 'ICS File Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribe_aggregator_default_ics_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via .ics files', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_post_statuses,
		),
		'tribe_aggregator_default_ics_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events imported via .ics files', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_categories,
		),
		'tribe_aggregator_default_ics_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$facebook = array(
		'facebook-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-facebook-settings">' . esc_html__( 'Facebook Import Settings', 'the-events-calendar' ) . '</h3>',
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
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$gcal = array(
		'gcal-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-google-settings">' . esc_html__( 'Google Calendar Import Settings', 'the-events-calendar' ) . '</h3>',
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
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$meetup = array(
		'meetup-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-meetup-settings">' . esc_html__( 'Meetup Import Settings', 'the-events-calendar' ) . '</h3>',
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
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
	);

	$url = array(
		'url-defaults' => array(
			'type' => 'html',
			'html' => '<h3 id="tribe-import-url-settings">' . esc_html__( 'Other URL Import Settings', 'the-events-calendar' ) . '</h3>',
		),
		'tribe_aggregator_default_url_post_status' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Status', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default post status for events imported via other URLs', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_post_statuses,
		),
		'tribe_aggregator_default_url_category' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Default Event Category', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'The default event category for events imported via other URLs', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_categories,
		),
		'tribe_aggregator_default_url_show_map' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Show Google Map', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'Show Google Map by default on imported event and venues', 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => '',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $origin_show_map_options,
		),
		'tribe_aggregator_default_url_import_range' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Import Date Range', 'the-events-calendar' ),
			'tooltip' => esc_html__( 'When importing from a website that uses The Events Calendar, the REST API will attempt to fetch events this far in the future. That website\'s hosting resources may impact the success of imports. Selecting a shorter time period may improve results.', 'the-events-calendar' ) . ' ' . sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_attr( 'https://theeventscalendar.com/knowledgebase/other-url-import-errors-in-event-aggregator' ), esc_html( 'Learn more.' ) ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => tribe( 'events-aggregator.settings' )->get_import_range_default(),
			'can_be_empty' => false,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => tribe( 'events-aggregator.settings' )->get_url_import_range_options( true ),
		),
		'tribe_aggregator_default_url_import_event_settings' => array(
			'type' => 'dropdown',
			'label' => esc_html__( 'Import Event Settings', 'the-events-calendar' ),
			'tooltip' => esc_html__( "Fetch source event's settings (e.g. Show Google Maps Link or Sticky in Month View) when importing from another site using The Events Calendar.", 'the-events-calendar' ),
			'size' => 'medium',
			'validation_type' => 'options',
			'default' => 'no',
			'can_be_empty' => true,
			'parent_option' => Tribe__Events__Main::OPTIONNAME,
			'options' => $yes_no_options,
		),
	);
}

$internal = array_merge(
	$change_authority,
	$global,
	$csv,
	$ical,
	$ics,
	$facebook,
	$gcal,
	$meetup,
	$url,
	$ea_disable
);

$internal = apply_filters( 'tribe_aggregator_fields', $internal );

if ( tribe( 'events-aggregator.main' )->is_service_active() ) {
	ob_start();
	?>
	<p>
		<?php
		printf(
			esc_html__(
				'Use the options below to configure your imports. Global Import Settings apply to all imports, but you can also override the global settings by adjusting the origin-specific options. Check your Event Aggregator Service Status on the %1$s.',
				'the-events-calendar'
			),
			'<a href="' . Tribe__Settings::instance()->get_url( array( 'page' => 'tribe-help' ) ) . '#tribe-tribe-aggregator-status">' . esc_html__( 'Help page', 'the-events-calendar' ) . '</a>'
		);
		?>
	</p>
	<div>
		<a href="#tribe-import-update-authority"><?php esc_html_e( 'Update Authority', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-global-settings"><?php esc_html_e( 'Global', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-csv-settings"><?php esc_html_e( 'CSV', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-ical-settings"><?php esc_html_e( 'iCalendar', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-ics-settings"><?php esc_html_e( 'ICS File', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-facebook-settings"><?php esc_html_e( 'Facebook', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-google-settings"><?php esc_html_e( 'Google Calendar', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-meetup-settings"><?php esc_html_e( 'Meetup', 'the-events-calendar' ); ?></a> |
		<a href="#tribe-import-url-settings"><?php esc_html_e( 'Other URLs', 'the-events-calendar' ); ?></a>
	</div>
	<?php
	$import_instructions = ob_get_clean();
} else {
	ob_start();
	?>
	<p><?php esc_html_e( 'Use the options below to configure your imports. Looking for more ways to import events from other websites?', 'the-events-calendar' ); ?></p>
	<a href="https://m.tri.be/196z"><?php esc_html_e( 'Check out Event Aggregator.', 'the-events-calendar' ); ?></a>
	<?php
	$import_instructions = ob_get_clean();
}

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
			'html' => '<p>' . $import_instructions . '</p>',
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
 * Allow developer to fully filter the Imports Tab contents
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
