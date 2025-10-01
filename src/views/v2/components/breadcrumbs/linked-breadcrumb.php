<?php

use Tribe__Utils__Array as Arr;

/**
 * View: Linked Breadcrumb
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/breadcrumbs/linked-breadcrumb.php
 *
 * See more documentation about our views templating system.
 *
 * @link	http://evnt.is/1aiy
 *
 * @version TBD
 * @since   6.2.0 Added classes and title attribute to the anchor tags.
 * @since   TBD Added W3C ARIA breadcrumb attributes for accessibility.
 *
 * @var array $breadcrumb {
 *                        *     Breadcrumb data.
 *                        *
 *                        *     @type string $label Breadcrumb text.
 *                        *     @type string $link URL for the breadcrumb link.
 *                        *     @type string $title Optional title attribute for the link.
 *                        *     @type bool $is_last True if this is the last breadcrumb.
 *                        * }
 */

if ( empty( $breadcrumb['label'] ) || empty( $breadcrumb['link'] ) ) {
	return;
}

$crumb_title = Arr::get( $breadcrumb, 'title', '' );
$crumb_link  = $breadcrumb['link'];
$crumb_label = $breadcrumb['label'];
$is_last     = $breadcrumb['is_last'] ?? false;
?>
<li class="tribe-events-c-breadcrumbs__list-item">
	<a
		href="<?php echo esc_url( $crumb_link ); ?>"
		class="tribe-events-c-breadcrumbs__list-item-link tribe-common-anchor"
		title="<?php echo esc_attr( $crumb_title ); ?>"
		<?php echo $is_last ? 'aria-current="page"' : ''; ?>
		data-js="tribe-events-view-link"
	>
		<?php echo esc_html( $crumb_label ); ?>
	</a>
	<?php if ( ! $is_last ) : ?>
		<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-breadcrumbs__list-item-icon-svg' ] ] ); ?>
	<?php endif; ?>
</li>
