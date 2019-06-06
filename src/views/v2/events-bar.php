<?php
/**
 * View: Events Bar
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
?>
<div class="tribe-events-c-events-bar">

	<h2 class="tribe-common-a11y-visual-hide"><?php printf( esc_html__( '%s Search and Views Navigation', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?></h2>

	<?php $this->template( 'events-bar/views' ); ?>

	<?php $this->template( 'events-bar/filters' ); ?>

	<?php $this->template( 'events-bar/form' ); ?>

</div>
