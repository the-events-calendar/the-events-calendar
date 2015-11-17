<?php
/**
 *  Widget admin for the this week widget.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
		<?php esc_html_e( 'Title:', 'tribe-events-calendar-pro' ); ?>
	</label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
	       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
	       value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>"/>
</p>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>">
		<?php esc_html_e( 'Layout:', 'tribe-events-calendar-pro' ); ?>
	</label>
	<select class="chosen layout-dropdown"
		id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"
		name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>"
		value="<?php echo esc_attr( $instance['layout'] ); ?>"
	>
		<option <?php selected( 'vertical', $instance['layout'] ) ?> value="vertical"> <?php echo esc_html_e( 'Vertical Layout', 'tribe-events-calendar-pro' ); ?></option>
		<option <?php selected( 'horizontal', $instance['layout'] ) ?> value="horizontal"> <?php echo esc_html_e( 'Horizontal Layout', 'tribe-events-calendar-pro' ); ?></option>
	</select>
</p>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'highlight_color' ) ); ?>"
	       style="display:block;"><?php esc_html_e( 'Highlight Color:', 'tribe-events-calendar-pro' ); ?></label>
	<input class="tribe-color-picker" rel="<?php echo esc_attr( $this->get_field_id( 'highlight_color' ) ); ?>"
	       type="text" id="<?php echo esc_attr( $this->get_field_id( 'highlight_color' ) ); ?>"
	       name="<?php echo esc_attr( $this->get_field_name( 'highlight_color' ) ); ?>"
	       value="<?php echo esc_attr( $instance['highlight_color'] ); ?>"/>
</p>

<script>
	( function ( $ ) {
		function init_color_picker( $widget ) {
			$widget.find( '.tribe-color-picker' ).wpColorPicker( {
				change: _.throttle( function () { // For Customizer
					$( this ).trigger( 'change' );
				}, 3000 )
			} );
		}

		function on_widget_update( event, $widget ) {
			init_color_picker( $widget );
		}

		$( document ).on( 'widget-added widget-updated', on_widget_update );
		$( function () {
			$( '#widgets-right .widget:has(.tribe-color-picker)' ).each( function () {
				init_color_picker( $( this ) );
			} );
		} );
	}( jQuery ) );
</script>

<p>
	<label
		for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of events to show per day:', 'tribe-events-calendar-pro' ); ?></label>
	<select class="chosen layout-dropdown" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
	        name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"
	        value="<?php echo esc_attr( $instance['count'] ); ?>">
		<?php for ( $i = 1; $i <= 10; $i ++ ) {
			?>
			<option <?php selected( $i, $instance['count'] ) ?>> <?php echo esc_attr( $i ); ?> </option>
		<?php } ?>
	</select>
</p>


<?php
/**
 * Filters
 */

$class = '';
if ( empty( $instance['filters'] ) ) {
	$class = 'display:none;';
}
?>

<div class="calendar-widget-filters-container" style="<?php echo esc_attr( $class ); ?>">

	<h3 class="calendar-widget-filters-title"><?php esc_html_e( 'Filters', 'tribe-events-calendar-pro' ); ?>:</h3>

	<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'filters' ) ); ?>"
	       id="<?php echo esc_attr( $this->get_field_id( 'filters' ) ); ?>" class="calendar-widget-added-filters"
	       value='<?php echo esc_attr( maybe_serialize( $instance['filters'] ) ); ?>'/>
	<style>
		.customizer-select2 {
			z-index : 500001
		}
	</style>
	<div class="calendar-widget-filter-list">
		<?php
		if ( ! empty( $instance['filters'] ) ) {

			echo '<ul>';

			foreach ( json_decode( $instance['filters'] ) as $tax => $terms ) {
				$tax_obj = get_taxonomy( $tax );

				foreach ( $terms as $term ) {
					if ( empty( $term ) ) {
						continue;
					}
					$term_obj = get_term( $term, $tax );
					if ( empty( $term_obj ) || is_wp_error( $term_obj ) ) {
						continue;
					}
					echo sprintf( "<li><p>%s: %s&nbsp;&nbsp;<span><a href='#' class='calendar-widget-remove-filter' data-tax='%s' data-term='%s'>(" . __( 'remove', 'tribe-events-calendar-pro' ) . ')</a></span></p></li>', esc_html( $tax_obj->labels->name ), esc_html( $term_obj->name ), esc_attr( $tax ), esc_attr( $term_obj->term_id ) );
				}
			}

			echo '</ul>';
		}
		?>

	</div>

	<p class="calendar-widget-filters-operand">
		<label for="<?php echo esc_attr( $this->get_field_name( 'operand' ) ); ?>">
			<input <?php checked( $instance['operand'], 'AND' ); ?> type="radio"
			                                                        name="<?php echo esc_attr( $this->get_field_name( 'operand' ) ); ?>"
			                                                        value="AND">
			<?php esc_html_e( 'Match all', 'tribe-events-calendar-pro' ); ?></label><br/>
		<label for="<?php echo esc_attr( $this->get_field_name( 'operand' ) ); ?>">
			<input <?php checked( $instance['operand'], 'OR' ); ?> type="radio"
			                                                       name="<?php echo esc_attr( $this->get_field_name( 'operand' ) ); ?>"
			                                                       value="OR">
			<?php esc_html_e( 'Match any', 'tribe-events-calendar-pro' ); ?></label>
	</p>
</div>
<p>
	<label><?php esc_html_e( 'Add a filter', 'tribe-events-calendar-pro' ); ?>:
		<select class="widefat calendar-widget-add-filter"
		        id="<?php echo esc_attr( $this->get_field_id( 'selector' ) ); ?>"
		        data-storage="<?php echo esc_attr( $this->get_field_id( 'filters' ) ); ?>">
			<?php
			echo "<option value='0'>" . esc_html__( 'Select one...', 'tribe-events-calendar-pro' ) . '</option>';
			foreach ( $taxonomies as $tax ) {
				echo sprintf( "<optgroup id='%s' label='%s'>", esc_attr( $tax->name ), esc_attr( $tax->labels->name ) );
				$terms = get_terms( $tax->name, array( 'hide_empty' => false ) );
				foreach ( $terms as $term ) {
					echo sprintf( "<option value='%d'>%s</option>", esc_attr( $term->term_id ), esc_html( $term->name ) );
				}
				echo '</optgroup>';
			}
			?>
		</select>
	</label>
</p>

<script type="text/javascript">

	jQuery( function ( $ ) {
		var filter = 'select.calendar-widget-add-filter:not(#widget-tribe-this-week-events-widget-__i__-selector)';

		if ( $( 'div.widgets-sortables' ).find( filter ).length && !$( '#customize-controls' ).length ) {

			$( ".select2-container.calendar-widget-add-filter" ).remove();
			setTimeout( function () {
				$( filter ).select2();
				calendar_toggle_all();
			}, 600 );
		}
	} );
</script>
