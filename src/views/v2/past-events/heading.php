<?php
/**
 * View: Past Event Heading
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/past-events/heading.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 */

$label = sprintf( __( 'Latest Past %s', 'the-events-calendar' ), tribe_get_event_label_plural() );
?>
<h2 class="tribe-events-calendar-past-events__heading">
	<?php echo esc_html( $label ); ?>
</h2>
