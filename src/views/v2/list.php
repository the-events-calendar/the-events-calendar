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
 * @version 4.9.9
 *
 * @var array  $events The array containing the events.
 * @var string $rest_url The REST URL.
 * @var string $rest_nonce The REST nonce.
 * @var int    $should_manage_url int containing if it should manage the URL.
 *
 */

?>
<div
	class="tribe-common tribe-events tribe-events-view tribe-events-view--list"
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

			<?php $this->template( 'list/top-bar' ); ?>
		</header>

		<?php $this->template( 'components/filter-bar' ); ?>

		<div class="tribe-events-calendar-list">

			<?php foreach ( $events as $event ) : ?>

				<?php $this->template( 'list/month-separator', [ 'event' => $event ] ); ?>

				<?php $this->template( 'list/event', [ 'event' => $event ] ); ?>

			<?php endforeach; ?>

		</div>

		<?php $this->template( 'list/nav' ); ?>
	</div>
</div>
