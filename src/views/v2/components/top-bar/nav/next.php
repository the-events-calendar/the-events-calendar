<?php
/**
 * View: Top Bar Navigation Next Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/nav/next.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $top_bar_next_label The label for the next page.
 *
 * @version TBD
 *
 */
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<a
		href="<?php echo esc_url( $next_url ); ?>"
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		aria-label="<?php echo esc_attr( $top_bar_next_label ); ?>"
		title="<?php echo esc_attr( $top_bar_next_label ); ?>"
		data-js="tribe-events-view-link"
	>
	</a>
</li>
