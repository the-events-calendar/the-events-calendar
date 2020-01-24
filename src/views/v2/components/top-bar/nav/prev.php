<?php
/**
 * View: Top Bar Navigation Previous Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/nav/prev.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $top_bar_prev_label The label for the previous page.
 *
 * @version TBD
 *
 */
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<a
		href="<?php echo esc_url( $prev_url ); ?>"
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="<?php esc_attr( $top_bar_prev_label ); ?>"
		title="<?php esc_attr( $top_bar_prev_label ); ?>"
		data-js="tribe-events-view-link"
	>
	</a>
</li>
