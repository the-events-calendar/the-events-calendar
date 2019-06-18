<?php
/**
 * View: Events Bar Views
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/views.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.3
 *
 */
use Tribe\Events\Views\V2\Manager;

$public_views = tribe( Manager::class )->get_publicly_visible_views();
$view_slug = $this->get( 'view' )->get_slug();
$view_label = $this->get( 'view' )->get_label();
?>
<div class="tribe-events-c-events-bar__views">
	<h3 class="tribe-common-a11y-visual-hide">
		<?php printf( esc_html__( '%s Views Navigation', 'the-events-calendar' ), tribe_get_event_label_singular() ); ?>
	</h3>
	<div class="tribe-events-c-view-selector tribe-events-c-view-selector--tabs" data-js="tribe-events-view-selector">
		<button
			class="tribe-events-c-view-selector__button"
			aria-controls="tribe-events-view-selector-content"
			aria-expanded="false"
			aria-selected="false"
			data-js="tribe-events-accordion-trigger"
		>
			<span class="tribe-events-c-view-selector__button-icon tribe-common-svgicon <?php echo sanitize_html_class( "tribe-common-svgicon--{$view_slug}" ); ?>"></span>
			<span class="tribe-events-c-view-selector__button-text">
				<?php echo esc_html( $view_label ); ?>
			</span>
		</button>
		<?php $this->template( 'events-bar/views/list', [ 'views' => $public_views ] ); ?>
	</div>
</div>
