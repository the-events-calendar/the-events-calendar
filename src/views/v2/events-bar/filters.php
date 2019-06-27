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
 * @version TBD
 *
 */

// @todo: This code will live in Filterbar. Move it, with this format.
// /*
?>
<div class="tribe-events-c-events-bar__filters">
	<h3 class="tribe-common-a11y-visual-hide">
		<?php printf( esc_html__( '%s Filters', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?>
	</h3>
	<button
		class="tribe-events-c-events-bar__filters-button tribe-common-b2"
		data-js="tribe-events-filters-button"
	>
		<?php esc_html_e( 'Filter', 'the-events-calendar' ); ?>
	</button>
	<div
		class="tribe-events-c-events-bar__filters-content"
		id="tribe-events-events-bar-filters"
		data-js="tribe-events-events-bar-filters"
	>
	</div>
</div>
