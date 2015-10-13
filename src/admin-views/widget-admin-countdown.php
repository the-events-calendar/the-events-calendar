<?php
/**
 * Widget admin for the event countdown widget.
 * @todo Apply Select2 for the Post Selection
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<div class="tribe-widget-countdown-container">
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'tribe-events-calendar-pro' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>" />
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Type:', 'tribe-events-calendar-pro' ); ?>
			<select data-no-search='1' class="widefat js-tribe-condition" data-tribe-conditional-field="type" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
				<option <?php selected( $instance['type'], 'single-event' ); ?> value="single-event"><?php esc_html_e( 'Single Event', 'tribe-events-calendar-pro' ); ?></option>
				<option <?php selected( $instance['type'], 'next-event' ); ?> value="next-event"><?php esc_html_e( 'Next Event', 'tribe-events-calendar-pro' ); ?></option>
			</select>
		</label>
	</p>

	<p class="js-tribe-conditional" data-tribe-conditional-field="type" data-tribe-conditional-value="single-event">
		<label for="<?php echo esc_attr( $this->get_field_id( 'event_ID' ) ); ?>"><?php esc_html_e( 'Event:', 'tribe-events-calendar-pro' ); ?>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'event_ID' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'event' ) ); ?>">
				<?php foreach ( $events as $event ): ?>
					<option value="<?php echo esc_attr( $event->ID ); ?>" <?php selected( $event->ID, $instance['event'] ) ?>><?php echo esc_attr( strip_tags( $event->post_title ) ); ?> - <?php echo esc_html( date_format( new DateTime( $event->EventStartDate ), 'm/d/Y' ) ); ?></option>
				<?php endforeach ?>
			</select>
		</label>
	</p>

	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'complete' ) ); ?>"><?php esc_html_e( 'Countdown Completed Text:', 'tribe-events-calendar-pro' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'complete' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'complete' ) ); ?>" type="text" value="<?php echo esc_attr( strip_tags( $instance['complete'] ) ); ?>" />
		<small class="js-tribe-conditional" data-tribe-conditional-field="type" data-tribe-conditional-value="next-event"><?php esc_html_e( 'On &#8220;Next Event&#8221; type of countdown, this text will only show when there are no events to show.', 'tribe-events-calendar-pro' ); ?></small>
	</p>

	<p>
		<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_seconds'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_seconds' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_seconds' ) ); ?>" />
		<label for="<?php echo esc_attr( $this->get_field_id( 'show_seconds' ) ); ?>"><?php esc_html_e( 'Show seconds?', 'tribe-events-calendar-pro' ); ?></label>
	</p>
</div>
