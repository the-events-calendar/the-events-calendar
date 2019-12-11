<?php
/**
 * View: Events Bar Views
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/views.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.11
 *
 * @var bool $disable_event_search Boolean on whether to disable the event search.
 */

/**
 * @todo: @bordoni can we move this to context?
 */
use Tribe\Events\Views\V2\Manager;

$public_views = tribe( Manager::class )->get_publicly_visible_views();
$view_slug = $this->get( 'view' )->get_slug();
$view_label = $this->get( 'view' )->get_label();

$is_tabs_style         = empty( $disable_event_search ) && 3 >= count( $public_views );
$view_selector_classes = [
	'tribe-events-c-view-selector'         => true,
	'tribe-events-c-view-selector--labels' => empty( $disable_event_search ),
	'tribe-events-c-view-selector--tabs'   => $is_tabs_style,
];
?>
<div class="tribe-events-c-events-bar__views">
	<h3 class="tribe-common-a11y-visual-hide">
		<?php printf( esc_html__( '%s Views Navigation', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?>
	</h3>
	<div <?php tribe_classes( $view_selector_classes ); ?> data-js="tribe-events-view-selector">
		<button
			class="tribe-events-c-view-selector__button"
			data-js="tribe-events-view-selector-button"
		>
			<span class="tribe-events-c-view-selector__button-icon tribe-common-svgicon <?php echo sanitize_html_class( "tribe-common-svgicon--{$view_slug}" ); ?>"></span>
			<span class="tribe-events-c-view-selector__button-text tribe-common-a11y-visual-hide">
				<?php echo esc_html( $view_label ); ?>
			</span>
		</button>
		<?php $this->template( 'components/events-bar/views/list', [ 'views' => $public_views ] ); ?>
	</div>
</div>
