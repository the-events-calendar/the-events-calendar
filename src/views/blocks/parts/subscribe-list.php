<?php
/**
 * Subscribe Dropdown Part.
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/blocks/parts/subscribe-list.php
 *
 * See more documentation about our Blocks Editor templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version 5.16.0
 *
 * @var array<Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract> $items Array containing subscribe/export objects.
 *
 */
if ( empty( $items ) ) {
	return;
}

remove_filter( 'the_content', 'do_blocks', 9 );

$default_classes = [ 'tribe-block', 'tribe-block__events-link' ];

// Add the custom classes from the block attributes.
$classes = isset( $attributes['className'] ) ? array_merge( $default_classes, [ $attributes['className'] ] ) : $default_classes;
?>
	<div <?php tribe_classes( $classes ); ?>>
		<div class="tribe-events tribe-common">
			<div class="tribe-events-c-subscribe-dropdown__container">
				<div class="tribe-events-c-subscribe-dropdown">
					<div class="tribe-common-c-btn-border tribe-events-c-subscribe-dropdown__button" tabindex="0">
						<?php $this->template( 'v2/components/icons/cal-export', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__export-icon' ] ] ); ?>
						<button class="tribe-events-c-subscribe-dropdown__button-text">
							<?php echo esc_html__( 'Add to calendar', 'the-events-calendar' ); ?>
						</button>
						<?php $this->template( 'v2/components/icons/caret-down', [ 'classes' => [ 'tribe-events-c-subscribe-dropdown__button-icon' ] ] ); ?>
					</div>
					<div class="tribe-events-c-subscribe-dropdown__content">
						<ul class="tribe-events-c-subscribe-dropdown__list" tabindex="0">
							<?php foreach ( $items as $item ) : ?>
								<li class="tribe-events-c-subscribe-dropdown__list-item">
									<a
										href="<?php echo esc_url( $item->get_uri( null ) ); ?>"
										class="tribe-events-c-subscribe-dropdown__list-item-link"
										tabindex="0"
										target="_blank"
										rel="noopener noreferrer nofollow noindex"
									>
										<?php echo esc_html( $item->get_label( null ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php add_filter( 'the_content', 'do_blocks', 9 );
