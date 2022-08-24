<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * @var array<Event_Report> $event_reports A list of the event report data.
 */
?>
<?php foreach ( $event_reports as $event ) : ?>
	<div class="tec-ct1-upgrade-event-item">
		<?php
		if ( $event->error ) {
			echo $event->error;
		} else {
			?>
			<a target="_blank"
			   href="<?php echo get_edit_post_link( $event->source_event_post->ID, false ) ?>"><?php echo esc_html( $event->source_event_post->post_title ); ?></a>
			<?php
		}
		?>
	</div>
<?php endforeach; ?>