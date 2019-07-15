<?php
/**
 * View: Month View
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month.php
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
	data-view-manage-url="<?php echo (int) $this->get( 'should_manage_url', true ); ?>"
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'loader', [ 'text' => 'Loading...' ] ); ?>

		<?php $this->template( 'data' ); ?>

		<header class="tribe-events-header">
			<?php $this->template( 'events-bar' ); ?>

			<?php $this->template( 'top-bar' ); ?>
		</header>

		<div
			class="tribe-events-calendar-month"
			role="grid"
			aria-labelledby="tribe-calendar-header"
			aria-readonly="true"
			data-js="tribe-events-month-grid"
		>

			<?php $this->template( 'month/calendar-header' ); ?>

			<?php $this->template( 'month/calendar-body' ); ?>

		</div>

		<?php $this->template( 'month/nav', [ 'location' => 'calendar' ] ); ?>

		<?php $this->template( 'month/mobile-events' ); ?>

	</div>

</div>
