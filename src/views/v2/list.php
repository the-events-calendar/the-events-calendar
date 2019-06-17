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
 * @version 4.9.3
 *
 */

use Tribe\Events\Views\V2\Rest_Endpoint;

/**
 * @todo  remove this and properly enqueue assets
 */
tribe_asset_enqueue( 'tribe-events-views-v2-manager' );
$events = $this->get( 'events' );
?>
<div
	class="tribe-common tribe-events"
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
	data-view-rest-url="<?php echo esc_url( tribe( Rest_Endpoint::class )->get_url() ); ?>"
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'loader', [ 'text' => 'Loading...' ] ); ?>

		<?php
		$this->template( 'data', [] );
		?>

		<?php $this->template( 'events-bar' ); ?>

		<?php $this->template( 'top-bar' ); ?>

		<div class="tribe-events-calendar-list">

			<?php $this->template( 'list/month-separator', [ 'month' => date( 'M' ) ] ); ?>

			<?php foreach ( $events as $event ) : ?>

				<?php $this->template( 'list/event', [ 'event' => $event ] ); ?>

			<?php endforeach; ?>

		</div>

		<?php $this->template( 'list/nav' ); ?>
	</div>
</div>