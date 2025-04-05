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
 * @link http://evnt.is/1aiy
 *
 * @version 6.2.0
 * @since 6.2.0 Added classes and title attribute to the anchor tags.
 *
 * @var array $breadcrumb Data for breadcrumb.
 */
$title = Arr::get( 'title', '' );
?>
<li class="tribe-events-c-breadcrumbs__list-item">
	<a
		href="<?php echo esc_url( $breadcrumb['link'] ); ?>"
		class="tribe-events-c-breadcrumbs__list-item-link tribe-common-anchor"
		title="<?php echo esc_attr( $title ); ?>"
		data-js="tribe-events-view-link"
	>
		<?php echo esc_html( $breadcrumb['label'] ); ?>
	</a>
	<?php $this->template( 'components/icons/caret-right', [ 'classes' => [ 'tribe-events-c-breadcrumbs__list-item-icon-svg' ] ] ); ?>
</li>
