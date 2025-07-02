<?php
/**
 * View: Events Bar Views
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/components/events-bar/views.php
 *
 * See more documentation about our views templating system.
 *
 * @link http://evnt.is/1aiy
 *
 * @version 6.12.0
 * @since 5.3.0
 * @since 6.12.0 Add aria-current and aria-label attributes to the view selector button for accessibility.
 *
 * @var string $view_slug            Slug of the current view.
 * @var string $view_label           Label of the current view.
 * @var array  $public_views         Array of data of the public views, with the slug as the key.
 * @var bool   $disable_event_search Boolean on whether to disable the event search.
 */

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
	<div <?php tec_classes( $view_selector_classes ); ?> data-js="tribe-events-view-selector">
		<button
			class="tribe-events-c-view-selector__button tribe-common-c-btn__clear"
			data-js="tribe-events-view-selector-button"
			aria-current="true"
			aria-description="<?php echo esc_attr__( 'Select Calendar View', 'the-events-calendar' ); ?>"
		>
			<span class="tribe-events-c-view-selector__button-icon">
				<?php $this->template( 'components/icons/' . esc_attr( $view_slug ), [ 'classes' => [ 'tribe-events-c-view-selector__button-icon-svg' ] ] ); ?>
			</span>
			<span class="tribe-events-c-view-selector__button-text tribe-common-a11y-visual-hide">
				<?php echo esc_html( $view_label ); ?>
			</span>
			<?php $this->template( 'components/icons/caret-down', [ 'classes' => [ 'tribe-events-c-view-selector__button-icon-caret-svg' ] ] ); ?>
		</button>
		<?php $this->template( 'components/events-bar/views/list' ); ?>
	</div>
</div>
