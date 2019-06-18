<?php
/**
 * View: Events Bar Views List Item
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/events-bar/views/item.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
use Tribe\Events\Views\V2\View;


// Bail on invalid name of class
if ( ! $this->get( 'view_class_name' ) ) {
	return;
}

$view_instance = View::make( $this->get( 'view_class_name' ) );
$view_slug = $view_instance->get_slug();
$is_current_view = $view->get_slug() === $view_instance->get_slug();
$view_url = tribe_events_get_url( [ 'eventDisplay' => $view_instance->get_slug() ], $this->get( 'view' )->get_url() );
?>
<li class="tribe-common-form-control-tabs__list-item" role="presentation">
	<input
		class="tribe-common-form-control-tabs__input"
		id="<?php echo sanitize_html_class( "tribe-views-{$view_slug}" ); ?>"
		name="tribe-views"
		type="radio"
		value="<?php echo esc_attr( $view_slug ); ?>"
		<?php checked( $is_current_view ); ?>
	/>
	<label
		class="tribe-common-form-control-tabs__label"
		id="<?php echo sanitize_html_class( "tribe-views-{$view_slug}-label" ); ?>"
		for="<?php echo sanitize_html_class( "tribe-views-{$view_slug}" ); ?>"
		role="option"
		aria-selected="true"
	>
		<?php echo esc_html( $view_instance->get_label() ); ?>
	</label>
</li>
