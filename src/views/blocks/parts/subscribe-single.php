<?php
/**
 * Subscribe Single Button Part.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/subscribe-single.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 5.16.0
 *
 * @var Link_Abstract $item Object containing subscribe/export label and url.
 */

use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;


if ( ! $item instanceof Link_Abstract ) {
	return;
}

remove_filter( 'the_content', 'do_blocks', 9 );

$default_classes = [ 'tribe-block', 'tribe-block__events-link' ];

// Add the custom classes from the block attributes.
$classes = isset( $attributes['className'] ) ? array_merge( $default_classes, [ $attributes['className'] ] ) : $default_classes;
?>
	<div <?php tec_classes( $classes ); ?>>
		<div class="tribe-events tribe-common">
			<div class="tribe-events-c-ical tribe-common-b2 tribe-common-b3--min-medium">
				<a
					class="tribe-events-c-ical__link"
					title="<?php echo esc_attr( $item->get_single_label() ); ?>"
					href="<?php echo esc_url( $item->get_uri() ); ?>"
					target="_blank"
					rel="noopener noreferrer nofollow noindex"
				>
					<?php $this->template( 'v2/components/icons/plus', [ 'classes' => [ 'tribe-events-c-ical__link-icon-svg' ] ] ); ?>
					<?php echo esc_html( $item->get_single_label() ); ?>
				</a>
			</div>
		</div>
	</div>

<?php add_filter( 'the_content', 'do_blocks', 9 );
