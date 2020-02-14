<?php
/**
 * View: Top Bar Navigation Previous Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/top-bar/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 *
 * @version 5.0.1
 *
 */
$label = sprintf( __( 'Previous %1$s', 'the-events-calendar' ), tribe_get_event_label_plural() );
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<a
		href="<?php echo esc_url( $prev_url ); ?>"
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="<?php echo esc_attr( $label ); ?>"
		title="<?php echo esc_attr( $label ); ?>"
		data-js="tribe-events-view-link"
	>
	</a>
</li>
