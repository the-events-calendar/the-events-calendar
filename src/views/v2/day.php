<?php
/**
 * View: Day View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

use Tribe\Events\Views\V2\Rest_Endpoint;

$events = $this->get( 'events' );
?>
<div
	class="tribe-common tribe-events tribe-events-view"
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
	data-view-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'loader', [ 'text' => 'Loading...' ] ); ?>

		<?php $this->template( 'data' ); ?>

		<header class="tribe-events-header">
			<?php $this->template( 'events-bar' ); ?>

			<?php $this->template( 'top-bar' ); ?>
		</header>

		<div class="tribe-events-calendar-day">

			<?php $this->template( 'day/time-separator' ); ?>

			<?php foreach ( $events as $event ) : ?>

				<?php $this->template( 'day/event', [ 'event' => $event ] ); ?>

			<?php endforeach; ?>

		</div>

		<?php $this->template( 'day/nav' ); ?>
	</div>

</div>
