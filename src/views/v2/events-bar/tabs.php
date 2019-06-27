<?php
/**
 * View: Events Bar Mobile Tabs
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/tabs.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

/**
 * @todo: show tabs only if filter bar is active
 */
?>
<div class="tribe-events-c-events-bar__tabs" role="tablist" data-js="tribe-events-events-bar-tablist">
	<button
		class="tribe-events-c-events-bar__tab tribe-events-c-events-bar__tab--search tribe-events-c-events-bar__tab--active"
		id="tribe-events-events-bar-search-tab"
		aria-controls="tribe-events-events-bar-search"
		data-js="tribe-events-events-bar-tab tribe-events-events-bar-search-tab"
		role="tab"
	>
		<span class="tribe-events-c-events-bar__tab-icon tribe-common-svgicon tribe-common-svgicon--search"></span>
		<span class="tribe-events-c-events-bar__tab-text tribe-common-b2">
			<?php esc_html_e( 'Search', 'the-events-calendar' ); ?>
		</span>
	</button>
	<button
		class="tribe-events-c-events-bar__tab tribe-events-c-events-bar__tab--filters"
		id="tribe-events-events-bar-filters-tab"
		aria-controls="tribe-events-events-bar-filters"
		data-js="tribe-events-events-bar-tab tribe-events-events-bar-filters-tab"
		role="tab"
	>
		<span class="tribe-events-c-events-bar__tab-icon tribe-common-svgicon tribe-common-svgicon--filters"></span>
		<span class="tribe-events-c-events-bar__tab-text tribe-common-b2">
			<?php esc_html_e( 'Filter', 'the-events-calendar' ); ?>
		</span>
	</button>
</div>
