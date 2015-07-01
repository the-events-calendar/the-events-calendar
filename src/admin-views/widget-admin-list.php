<?php
/**
 * Widget admin for the event list widget.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'tribe-events-calendar' ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Show:', 'tribe-events-calendar' ); ?></label>
	<select id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" class="widefat">
		<?php for ( $i = 1; $i <= 10; $i ++ ) {
			?>
			<option <?php if ( $i == $instance['limit'] ) {
				echo 'selected="selected"';
			} ?> > <?php echo $i; ?> </option>
		<?php } ?>
	</select>
</p>
<label for="<?php echo esc_attr( $this->get_field_id( 'no_upcoming_events' ) ); ?>"><?php esc_html_e( 'Show widget only if there are upcoming events:', 'tribe-events-calendar' ); ?></label>
<input id="<?php echo esc_attr( $this->get_field_id( 'no_upcoming_events' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'no_upcoming_events' ) ); ?>" type="checkbox" <?php checked( $instance['no_upcoming_events'], 1 ); ?> value="1" />
<p>

</p>
