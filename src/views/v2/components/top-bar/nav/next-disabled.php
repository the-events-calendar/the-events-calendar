<?php
/**
 * View: Top Bar Navigation Next Disabled Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/nav/next-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $top_bar_next_label The label for the next page.
 *
 * @version TBD
 *
 */
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-right tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--next"
		aria-label="<?php echo esc_attr( $top_bar_next_label ); ?>"
		title="<?php echo esc_attr( $top_bar_next_label ); ?>"
		disabled
	>
	</button>
</li>
