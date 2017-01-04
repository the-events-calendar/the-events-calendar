<?php


class Tribe__Events__REST__V1__Endpoints__Base {

	/**
	 * @var Tribe__Events__REST__V1__Messages
	 */
	protected $messages;

	public function __construct( Tribe__Events__REST__V1__Messages $messages ) {
		$this->messages = $messages;
	}
}