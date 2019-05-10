<?php
/**
 * View: List View Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
?>
<nav
	class="tribe-common-c-nav"
	aria-label="<?php echo esc_html( sprintf( esc_html__( '%s List Navigation', 'the-events-calendar' ), tribe_get_event_label_plural() ) ); ?>"
>
	<?php $this->template( 'list/nav/prev' ); ?>

	<?php $this->template( 'list/nav/next' ); ?>

</nav>