<?php
/**
 * View: List View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/list.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
use Tribe\Events\Views\V2\Rest_Endpoint;
$events_label_plural = tribe_get_event_label_plural();
?>
<form
	class="tribe-common tribe-events tribe-events-container"
	action=""
	method="get"
	data-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<?php $this->template( 'update-url-script', [ 'url' => home_url( 'events/list/page/3' ), 'title' => 'Developers title' ] ); ?>

	<?php $this->template( 'events-bar' ); ?>

	<?php $this->template( 'top-bar' ); ?>

	<div class="tribe-events-calendar-list">

		<?php $this->template( 'list/month-separator', [ 'month' => date( 'M' ) ] ); ?>

		<?php foreach ( $events as $event ) : ?>

			<?php $this->template( 'list/single-event', [ 'event' => $event ] ); ?>

		<?php endforeach; ?>

	</div>

	<?php $this->template( 'list/nav' ); ?>
</form>
