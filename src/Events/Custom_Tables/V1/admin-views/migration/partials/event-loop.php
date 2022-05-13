<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string  $template_directory   The path to the template directory.
 * @var string  $event_category_key   The key of the category this list is for.
 * @var string  $event_category_label The label of the category this list is for.
 * @var boolean $has_upcoming         Whether to add upcoming events paginate button.
 * @var boolean $has_past             Whether to add past events paginate button.
 */
?>
	<span>
		<strong><?php echo esc_html( $event_category_label ); ?></strong>
	</span>
	<ul class="tec-ct1-upgrade-events-container tec-ct1-upgrade-events-category-<?php echo esc_attr( $event_category_key ); ?>">
		<?php
		include( $template_directory . '/partials/event-items.php' );
		?>
	</ul>
<?php
if ( $has_past ) {
	?>
	<a
			href="#"
			data-events-paginate-category="<?php echo esc_attr( $event_category_key ); ?>"
			data-events-paginate="1"
	>Show past events</a>
	<?php
}
if ( $has_past && $has_upcoming ) {
	echo " | ";
}
if ( $has_upcoming ) {
	?>
	<a
			href="#"
			data-events-paginate-category="<?php echo esc_attr( $event_category_key ); ?>"
			data-events-paginate-upcoming="1"
			data-events-paginate="1"
	>Show more upcoming events</a>
	<?php
}
?>