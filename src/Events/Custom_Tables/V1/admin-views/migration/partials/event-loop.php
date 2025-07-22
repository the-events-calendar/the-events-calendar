<?php

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string  $template_directory   The path to the template directory.
 * @var string  $event_category_key   The key of the category this list is for.
 * @var string  $event_category_label The label of the category this list is for.
 * @var boolean $has_upcoming         Whether to add upcoming events paginate button.
 * @var boolean $has_past             Whether to add past events paginate button.
 * @var int     $past_start_page      What page to start at for pagination requests.
 * @var int     $upcoming_start_page  What page to start at for pagination requests.
 */
?>
<div class="tec-ct1-upgrade-events-category-container">
	<span>
		<strong><?php echo wp_kses( $event_category_label, [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></strong>
	</span>
	<div class="tec-ct1-upgrade-events-container tec-ct1-upgrade-events-category-<?php echo esc_attr( $event_category_key ); ?>">
		<?php
		include( $template_directory . '/partials/event-items.php' );
		?>
	</div>
	<?php
	if ( $has_past || $has_upcoming ) {
		?>
		<div class="tec-ct1-upgrade-events-pagination-buttons-container">
			<?php
			if ( $has_past ) {
				?>
				<a
						href="#"
						data-events-paginate-category="<?php echo esc_attr( $event_category_key ); ?>"
						data-events-paginate="1"
						data-events-paginate-start-page="<?php echo $past_start_page; ?>"
				><?php echo esc_html( sprintf( __( 'Show past %1$s', 'the-events-calendar' ), tribe_get_event_label_plural_lowercase() ) ); ?></a>
				<?php
			}
			if ( $has_past && $has_upcoming ) {
				?>
				<span class='tec-ct1-upgrade-migration-pagination-separator'> | </span>
				<?php
			}
			if ( $has_upcoming ) {
				?>
				<a
						href="#"
						data-events-paginate-category="<?php echo esc_attr( $event_category_key ); ?>"
						data-events-paginate-upcoming="1"
						data-events-paginate-start-page="<?php echo $upcoming_start_page; ?>"
						data-events-paginate="1"
				><?php echo esc_html( sprintf( __( 'Show more upcoming %1$s', 'the-events-calendar' ), tribe_get_event_label_plural_lowercase() ) ); ?></a>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
</div>