<?php
/**
 * View: List View Month separator
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list/month-separator.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
$date  = $this->get( 'date' );
$month = date( 'M', $date );
$year  = date( 'Y', $date );
?>
<div class="tribe-events-calendar-list__month-separator">
	<time
		class="tribe-events-calendar-list__month-separator-text tribe-common-h7 tribe-common-h7--alt"
		datetime="<?php echo esc_attr( $year ); ?>-<?php echo esc_attr( date( 'm', $date ) ); ?>"
	>
		<?php echo esc_html( $month ); ?> <?php echo esc_html( $year ); ?>
	</time>
</div>
