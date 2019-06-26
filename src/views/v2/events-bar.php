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
 * @version TBD
 *
 */
$classes = [ 'tribe-events-header__events-bar', 'tribe-events-c-events-bar' ];

/**
 * @todo: add in once we have 'has filter bar' logic
 */
// if ( $has_filter_bar ) {
// 	$classes[] = 'tribe-events-c-events-bar--has-filter-bar';
// }
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<h2 class="tribe-common-a11y-visual-hide"><?php printf( esc_html__( '%s Search and Views Navigation', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?></h2>

	<?php $this->template( 'events-bar/search' ); ?>

	<?php $this->template( 'events-bar/filters' ); ?>

	<?php $this->template( 'events-bar/views' ); ?>

</div>
