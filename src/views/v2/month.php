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
 * @version TBD
 *
 * @var string $rest_url The REST URL.
 * @var string $rest_nonce The REST nonce.
 * @var int    $should_manage_url int containing if it should manage the URL.
 */
?>
<div
	class="tribe-common tribe-events tribe-events-view tribe-events-view--month"
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( $rest_nonce ); ?>"
	data-view-rest-url="<?php echo esc_url( $rest_url ); ?>"
	data-view-manage-url="<?php echo esc_attr( $should_manage_url ); ?>"
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'components/loader', [ 'text' => __( 'Loading...', 'the-events-calendar' ) ] ); ?>

		<?php $this->template( 'components/data' ); ?>

		<header class="tribe-events-header">
			<?php $this->template( 'components/events-bar' ); ?>

			<?php $this->template( 'month/top-bar' ); ?>
		</header>

		<?php $this->template( 'components/filter-bar' ); ?>

		<div
			class="tribe-events-calendar-month"
			role="grid"
			aria-labelledby="tribe-events-calendar-header"
			aria-readonly="true"
			data-js="tribe-events-month-grid"
		>

			<?php $this->template( 'month/calendar-header' ); ?>

			<?php $this->template( 'month/calendar-body' ); ?>

		</div>

		<?php $this->template( 'month/mobile-events' ); ?>

	</div>

</div>
