<?php
/**
 * View: Breadcrumbs
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breadcrumbs.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.11
 *
 * @var array $breadcrumbs An array of data for breadcrumbs.
 */

if ( empty( $breadcrumbs ) ) {
	return;
}
?>
<div class="tribe-events-header__breadcrumbs tribe-events-c-breadcrumbs">
	<ol class="tribe-events-c-breadcrumbs__list">
		<?php foreach ( $breadcrumbs as $breadcrumb ) : ?>

			<?php if ( ! empty( $breadcrumb['link'] ) ) : ?>
				<?php $this->template( 'components/breadcrumbs/linked-breadcrumb', [ 'breadcrumb' => $breadcrumb ] ); ?>
			<?php else : ?>
				<?php $this->template( 'components/breadcrumbs/breadcrumb', [ 'breadcrumb' => $breadcrumb ] ); ?>
			<?php endif; ?>

		<?php endforeach; ?>
	</ol>
</div>
