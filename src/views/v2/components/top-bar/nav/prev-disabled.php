<?php
/**
 * View: Top Bar Navigation Previous Disabled Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/components/top-bar/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $link The URL to the previous page, if any, or an empty string.
 *
 * @version TBD
 *
 */
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="<?php esc_html_e( 'Prev', 'the-events-calendar' ); ?>"
		title="<?php esc_html_e( 'Prev', 'the-events-calendar' ); ?>"
		disabled
	>
	</button>
</li>
