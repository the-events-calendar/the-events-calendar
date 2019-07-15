<?php
/**
 * View: Events Bar Search Submit Input
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/search/submit.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
?>
<button
	class="tribe-common-c-btn tribe-common-c-search__button"
	type="submit"
	name="submit-bar"
><?php printf( esc_html__( 'Find %s', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?></button>
