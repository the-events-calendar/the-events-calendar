<?php


abstract class Tribe__Events__REST__V1__Endpoints__Base {

	/**
	 * @var Tribe__REST__Messages_Interface
	 */
	protected $messages;

	public function __construct( Tribe__REST__Messages_Interface $messages ) {
		$this->messages = $messages;
	}

	/**
	 * @param WP_REST_Request $request
	 * @param                 $slug
	 * @return bool|false|string
	 * @throws Tribe__REST__Exceptions__Exception
	 */
	protected function parse_date_value( WP_REST_Request $request, $slug ) {
		if ( ! empty( $request[ $slug ] ) ) {
			$start_date = strtotime( $request[ $slug ] );
			// Unix timestamp is a thing...
			$start_date = $start_date ? $start_date : $request[ $slug ];
			// at this point if it's legit it should be a number
			if ( ! is_numeric( $start_date ) ) {
				$message = $this->messages->get_message( "event-archive-bad-{$slug}" );

				throw new Tribe__REST__Exceptions__Exception( "event-archive-bad-{$slug}", $message, 400 );
			}
			try {
				return date( Tribe__Date_Utils::DBDATETIMEFORMAT, $start_date );
			} catch ( Exception $e ) {
				$message = $this->messages->get_message( "event-archive-bad-{$slug}" );

				throw new Tribe__REST__Exceptions__Exception( "event-archive-bad-{$slug}", $message, 400 );
			}
		}

		return false;
	}
}