<?php
use Tribe\Events\Admin\Settings as Plugin_Settings;

class Tribe__Events__REST__V1__EA_Messages extends Tribe__Events__REST__V1__Messages implements Tribe__REST__Messages_Interface {
	public function __construct() {
		parent::__construct();

		// TEC REST v1 messages will all have the `rest-v1:` prefix applied
		$ea_messages = [
			'not-tec-rest-api-site'              => __( 'Event Aggregator cannot import events from this site.', 'the-events-calendar' ),
			'pre-45-tec-site'                    => __( 'Event Aggregator cannot import events because this site is running an outdated version of The Events Calendar.', 'the-events-calendar' ),
			'tec-rest-api-missing-origin'        => __( 'The Events Calendar is API is not providing the site origin correctly.', 'the-events-calendar' ),
			'tec-rest-api-unsupported'           => __( 'Events could not be imported. Event Aggregator does not yet support events from that URL. We have noted your request and will review it for support in the future.', 'the-events-calendar' ),
			'tec-rest-api-disabled'              => __( 'Events could not be imported. The Events Calendar REST API is disabled on the requested URL.', 'the-events-calendar' ),
			'tec-rest-api-bad-data'              => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but returned malformed data.', 'the-events-calendar' ),
			'tec-rest-api-archive-header-error'  => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but there was an error while fetching the archive control data.', 'the-events-calendar' ),
			'tec-rest-api-archive-missing-total' => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but there was an error while fetching the total number of events.', 'the-events-calendar' ),
			'tec-rest-api-archive-bad-total'     => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but returned malformed data in regard to the total number of events.', 'the-events-calendar' ),
			'tec-rest-api-archive-page-error'    => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but there was an error while fetching an archive page.', 'the-events-calendar' ),
			'tec-rest-api-archive-page-empty'    => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but returned an empty archive page.', 'the-events-calendar' ),
			'tec-rest-api-single-event-error'    => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but there was an error while fetching the event data.', 'the-events-calendar' ),
			'tec-rest-api-single-event-empty'    => __( 'Events could not be imported. The URL provided could be reached and has The Events Calendar REST API enabled, but returned empty event data.', 'the-events-calendar' ),
			'no-results'                         => __( 'The requested URL does not have any upcoming and published events matching the search criteria.', 'the-events-calendar' ),
		];

		$learn_more_map = [
			'tec-rest-api-unsupported',
			'tec-rest-api-disabled',
			'tec-rest-api-bad-data',
			'tec-rest-api-archive-header-error',
			'tec-rest-api-archive-missing-total',
			'tec-rest-api-archive-bad-total',
			'tec-rest-api-archive-page-error',
			'tec-rest-api-archive-page-empty',
			'tec-rest-api-single-event-error',
			'tec-rest-api-single-event-empty',
		];

		$learn_more_link = esc_attr( 'https://theeventscalendar.com/knowledgebase/url-import-errors-event-aggregator/' );
		$learn_more_message = esc_html__( 'Learn more.', 'the-events-calendar' );
		$learn_more_message_html = sprintf( '<a href="%s" target="_blank">%s</a> ', $learn_more_link, $learn_more_message );

		foreach ( $learn_more_map as $message_code ) {
			$ea_messages[ $message_code ] .= ' ' . $learn_more_message_html;
		}

		$adjustable_map = [
			'tec-rest-api-bad-data',
			'tec-rest-api-archive-header-error',
			'tec-rest-api-archive-missing-total',
			'tec-rest-api-archive-bad-total',
			'tec-rest-api-archive-page-error',
			'tec-rest-api-archive-page-empty',
			'tec-rest-api-single-event-error',
			'tec-rest-api-single-event-empty',
		];

		$adjust_link         = esc_url( tribe( Plugin_Settings::class )->get_url( [ 'tab' => 'imports#tribe-field-tribe_aggregator_default_url_import_range' ] ) );
		$adjust_message      = esc_html__( 'Try to adjust your import settings and try again.', 'the-events-calendar' );
		$adjust_message_html = sprintf( '<p><a href="%s" target="_blank">%s</a></p> ', $adjust_link, $adjust_message );

		foreach ( $adjustable_map as $message_code ) {
			$ea_messages[ $message_code ] .= ' ' . $adjust_message_html;
		}

		$this->messages = array_merge( $this->messages, $ea_messages );
	}
}
