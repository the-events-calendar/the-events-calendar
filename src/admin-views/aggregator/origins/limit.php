<?php
/** @var \Tribe__Events__Aggregator__Settings $settings */
$settings             = tribe( 'events-aggregator.settings' );
$global_limit_type    = tribe_get_option( 'tribe_aggregator_default_import_limit_type', 'range' );

if ( 'no_limit' === $global_limit_type ) {
	return;
}

$global_limit_option  = $global_limit_type === 'range'
	? tribe_get_option( 'tribe_aggregator_default_import_limit_range', $settings->get_import_range_default() )
	: tribe_get_option( 'tribe_aggregator_default_import_limit_number', $settings->get_import_limit_count_default() );
$global_limit_strings = $global_limit_type === 'range'
	? $settings->get_import_range_options( false )
	: $settings->get_import_limit_count_options();

$global_limit_string  = $global_limit_strings[ $global_limit_option ];
$global_limit_message = $global_limit_type === 'range'
	? esc_html( sprintf( __( 'Event Aggregator will try to fetch events starting within the next %s from the current date or the specified date;', 'the-events-calendar' ), $global_limit_string ) )
	: esc_html( sprintf( __( 'Event Aggregator will try to fetch %s events starting from the current date or the specified date;', 'the-events-calendar' ), $global_limit_string ) );
$import_limi_link     = esc_attr( admin_url( '/edit.php?post_type=tribe_events&page=tribe-common&tab=imports#tribe-field-tribe_aggregator_default_import_limit_type' ) );
$import_limit_message = $global_limit_message . ' ' . sprintf( '<a href="%s" target="_blank">%s</a> ', $import_limi_link, esc_html__( 'you can modify this setting here.', 'the-events-calendar' ) );
?>

<div class="tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition-not-empty data-condition-not="url" data-condition-relation="and">
	<p><?php echo $import_limit_message; ?></p>
</div>
