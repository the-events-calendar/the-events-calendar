<?php
/**
 * View: Events Bar Filters
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/filters.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-c-events-bar__filters">
	<div class="tribe-events-c-events-bar__filters-button-wrapper tribe-events-c-events-bar__filters-button-wrapper--search">
		<button
			class="tribe-common-c-btn-icon tribe-common-c-btn-icon--search tribe-events-c-events-bar__filters-button tribe-events-c-events-bar__filters-button--search"
			aria-label="<?php esc_html_e( 'Search', 'the-events-calendar' ); ?>"
			title="<?php esc_html_e( 'Search', 'the-events-calendar' ); ?>"
		>
		</button>
	</div>
	<div class="tribe-events-c-events-bar__filters-button-wrapper tribe-events-c-events-bar__filters-button-wrapper--filter">
		<button
			class="tribe-common-c-btn-icon tribe-common-c-btn-icon--filters tribe-events-c-events-bar__filters-button tribe-events-c-events-bar__filters-button--filter"
			aria-label="<?php esc_html_e( 'Filter', 'the-events-calendar' ); ?>"
			title="<?php esc_html_e( 'Filter', 'the-events-calendar' ); ?>"
		>
		</button>
	</div>
</div>
