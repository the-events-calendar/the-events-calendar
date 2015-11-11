<?php


class Tribe__Events__Pro__Recurrence__Admin_Notices {

	/**
	 * @var  self
	 */
	protected static $instance;
	/**
	 * @var
	 */
	private $notice;

	/**
	 * Tribe__Events__Pro__Recurrence__Admin_Notices constructor.
	 *
	 * @param Tribe__Events__Main__Admin__Notices__Notice_Interface|null $notice
	 */
	public function __construct( Tribe__Events__Admin__Notices__Notice_Interface $notice = null ) {
		$this->notice = $notice ? $notice : new Tribe__Events__Admin__Notices__Base_Notice();
	}

	/**
	 * Singleton constructor for the class.
	 *
	 * @return Tribe__Events__Pro__Recurrence__Admin_Notices
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Displays a message informing the user she is editing all of the recurrences in the series.
	 */
	public function display_editing_all_recurrences_notice() {
		$message = __( 'You are currently editing all events in a recurring series.', 'tribe-events-calendar-pro' );

		$this->notice->render( $message, 'updated' );
	}

	public function display_created_recurrences_notice() {
		$pending = get_post_meta( get_the_ID(), '_EventNextPendingRecurrence', true );

		if ( ! $pending ) {
			return;
		}

		$start_dates     = tribe_get_recurrence_start_dates( get_the_ID() );
		$count           = count( $start_dates );
		$last            = end( $start_dates );
		$pending_message = __( '%d instances of this event have been created through %s. <a href="%s">Learn more.</a>', 'tribe-events-calendar-pro' );
		$pending_message = sprintf( $pending_message, $count, date_i18n( tribe_get_date_format( true ), strtotime( $last ) ), 'http://m.tri.be/lq' );

		$this->notice->render( $pending_message, 'updated' );
	}
}