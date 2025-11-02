<?php
/**
 * View: Breadcrumb without link
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breadcrumbs/breadcrumb.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.15.7
 *
 * @since 6.15.7 Added W3C ARIA breadcrumb attributes for accessibility.
 *
 * @var array   $breadcrumb {
 *                          Breadcrumb data.
 *
 * @type string $label      Breadcrumb text.
 * @type bool   $is_last    True if this is the last breadcrumb.
 *                          }
 */

if ( empty( $breadcrumb['label'] ) ) {
	return;
}

$label   = $breadcrumb['label'];
$is_last = $breadcrumb['is_last'] ?? false;
?>
<li class="tribe-events-c-breadcrumbs__list-item">
	<span
		class="tribe-events-c-breadcrumbs__list-item-text"
		<?php echo $is_last ? 'aria-current="page"' : ''; ?>
	>
		<?php echo esc_html( $label ); ?>
	</span>
	<?php if ( ! $is_last ) : ?>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-breadcrumbs__list-item-icon-svg' ] ] ); ?>
	<?php endif; ?>
</li>
