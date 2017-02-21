<?php

class Tribe__Events__REST__V1__EA_Messages extends Tribe__Events__REST__V1__Messages implements Tribe__REST__Messages_Interface
{
	public function __construct()
	{
		parent::__construct();

		$ea_messages = array(
			'not-tec-rest-api-site'              => __( 'The Events Calendar is not active or is not at least version 4.5 on the requested URL.', 'the-events-calendar' ),
			'tec-rest-api-unsupported'           => __( 'The requested URL does not support The Events Calendar REST API.', 'the-events-calendar' ),
			'tec-rest-api-disabled'              => __( 'The Events Calendar REST API is disabled on the requested URL.', 'the-events-calendar' ),
			'tec-rest-api-bad-data'              => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but returned malformed data.', 'the-events-calendar' ),
			'tec-rest-api-archive-header-error'  => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but there was an error while fetching the archive control data.', 'the-events-calendar' ),
			'tec-rest-api-archive-missing-total' => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but there was an error while fetching the total number of events.', 'the-events-calendar' ),
			'tec-rest-api-archive-bad-total'     => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but returned malformed data in regard to the total number of events.', 'the-events-calendar' ),
			'tec-rest-api-archive-page-error'    => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but there was an error while fetching an archive page.', 'the-events-calendar' ),
			'tec-rest-api-archive-page-empty'    => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but returned an empty archive page.', 'the-events-calendar' ),
			'tec-rest-api-single-event-error'    => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but there was an error while fetching the event data.', 'the-events-calendar' ),
			'tec-rest-api-single-event-empty'    => __( 'The URL provided could be reached and has The Events Calendar REST API enabled but returned empty event data.', 'the-events-calendar' ),
			'no-results'                         => __( 'The requested URL does not have any upcoming and published events matching the search criteria.', 'the-events-calendar' ),
		);

		$this->messages = array_merge( $this->messages, $ea_messages );
	}
}