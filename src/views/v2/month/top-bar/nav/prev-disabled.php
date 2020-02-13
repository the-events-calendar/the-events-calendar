<?php
/**
 * View: Top Bar Navigation Previous Disabled Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/top-bar/nav/prev-disabled.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.1
 *
 */
?>
<li class="tribe-events-c-top-bar__nav-list-item">
	<button
		class="tribe-common-c-btn-icon tribe-common-c-btn-icon--caret-left tribe-events-c-top-bar__nav-link tribe-events-c-top-bar__nav-link--prev"
		aria-label="<?php esc_attr_e( 'Previous month', 'the-events-calendar' ); ?>"
		title="<?php esc_attr_e( 'Previous month', 'the-events-calendar' ); ?>"
		disabled
	>
	</button>
</li>
