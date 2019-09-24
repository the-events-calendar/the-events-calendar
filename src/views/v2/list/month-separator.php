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
<<<<<<< HEAD
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
=======
 * @version 4.9.9
>>>>>>> 5f299a2c51a558e84ebfb43592a13187cad64388
 */

use Tribe\Events\Views\V2\Utils;

$should_have_month_separator = Utils\Separators::should_have_month( $this->get( 'events' ), $event );

if ( ! $should_have_month_separator ) {
	return;
}

?>
<div class="tribe-events-calendar-list__month-separator">
	<time
		class="tribe-events-calendar-list__month-separator-text tribe-common-h7 tribe-common-h6--min-medium tribe-common-h--alt"
		datetime="<?php echo esc_attr( $event->dates->start->format( 'Y-m' ) ); ?>"
	>
		<?php echo esc_html( $event->dates->start->format( 'F Y' ) ); ?>
	</time>
</div>
