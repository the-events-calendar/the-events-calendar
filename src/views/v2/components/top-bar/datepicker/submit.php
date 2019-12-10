<?php
/**
 * View: Top Bar - Datepicker Submit
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/top-bar/datepicker/submit.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.13
 *
 * @var bool $show_datepicker_submit Boolean on whether to show the datepicker submit button.
 *
 */

if ( empty( $show_datepicker_submit ) ) {
	return;
}
?>
<button
	class="tribe-common-c-btn tribe-common-a11y-hidden tribe-events-c-top-bar__datepicker-submit"
	type="submit"
	name="submit-bar"
>
	<?php printf( esc_html__( 'Find %s', 'the-events-calendar' ), tribe_get_event_label_plural() ); ?>
</button>
