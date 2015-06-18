<?php
/**
 * Widget admin for the event countdown widget.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'tribe-events-calendar-pro' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>" />
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'event_ID' ) ); ?>"><?php esc_html_e( 'Event:', 'tribe-events-calendar-pro' ); ?></label>
	<select class="chosen events-dropdown" id="<?php echo esc_attr( $this->get_field_id( 'event_ID' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'event' ) ); ?>">
		<?php foreach ( $events as $event ): ?>
			<option value="<?php echo esc_attr( $event->ID ); ?>|<?php echo date_format( new DateTime( $event->EventStartDate ), Tribe__Events__Date_Utils::DBDATEFORMAT ); ?>" <?php selected( $event->ID . '|' . date_format( new DateTime( $event->EventStartDate ), Tribe__Events__Date_Utils::DBDATEFORMAT ) == $instance['event_ID'] . '|' . $instance['event_date'] ) ?>><?php echo esc_attr( strip_tags( $event->post_title ) ); ?> - <?php echo date_format( new DateTime( $event->EventStartDate ), 'm/j/Y' ); ?></option>
		<?php endforeach ?>
	</select>
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_seconds' ) ); ?>"><?php esc_html_e( 'Show seconds:', 'tribe-events-calendar-pro' ); ?></label>

	<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_seconds'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_seconds' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_seconds' ) ); ?>" />
</p>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'complete' ) ); ?>"><?php esc_html_e( 'Countdown Completed Text:', 'tribe-events-calendar-pro' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'complete' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'complete' ) ); ?>" type="text" value="<?php echo esc_attr( strip_tags( $instance['complete'] ) ); ?>" />
</p>
