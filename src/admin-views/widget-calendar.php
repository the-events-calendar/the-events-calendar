<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'tribe-events-calendar-pro' ); ?>
		<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>" />
	</label>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of events to show:', 'tribe-events-calendar-pro' ); ?>
		<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'count' ); ?>"
		       id="<?php echo $this->get_field_id( 'count' ); ?>"
		       value="<?php echo esc_attr( strip_tags( $instance['count'] ) ); ?>" />
	</label>
</p>

<?php
$class = "";
if ( empty( $instance['filters'] ) ) {
	$class = "display:none;";
}
?>

<div class="calendar-widget-filters-container" style="<?php echo $class; ?>">

	<h3 class="calendar-widget-filters-title"><?php _e( 'Filters', 'tribe-events-calendar-pro' ); ?>:</h3>

	<input type="hidden" name="<?php echo $this->get_field_name( 'filters' ); ?>"
	       id="<?php echo $this->get_field_id( 'filters' ); ?>" class="calendar-widget-added-filters"
	       value='<?php echo maybe_serialize( $instance['filters'] ); ?>' />

	<div class="calendar-widget-filter-list">

		<?php
		if ( ! empty( $instance['filters'] ) ) {

			foreach ( json_decode( $instance['filters'] ) as $tax => $terms ) {
				$tax_obj = get_taxonomy( $tax );

				foreach ( $terms as $term ) {
					if ( empty( $term ) ) {
						continue;
					}
					$term_obj = get_term( $term, $tax );
					echo sprintf( "<li><p>%s: %s&nbsp;&nbsp;<span><a href='#' class='calendar-widget-remove-filter' data-tax='%s' data-term='%s'>(" . __( 'remove', 'tribe-events-calendar-pro' ) . ")</a></span></p></li>", $tax_obj->labels->name, $term_obj->name, $tax, $term_obj->term_id );
				}
			}
		}
		?>

	</div>

	<p class="calendar-widget-filters-operand">
		<label for="<?php echo $this->get_field_name( 'operand' ); ?>">
			<input <?php checked( $instance['operand'], 'AND' ); ?> type="radio" name="<?php echo $this->get_field_name( 'operand' ); ?>" value="AND">
			<?php _e( 'Match all', 'tribe-events-calendar-pro' ); ?></label><br />
		<label for="<?php echo $this->get_field_name( 'operand' ); ?>">
			<input <?php checked( $instance['operand'], 'OR' ); ?> type="radio" name="<?php echo $this->get_field_name( 'operand' ); ?>" value="OR">
			<?php _e( 'Match any', 'tribe-events-calendar-pro' ); ?></label>
	</p>
</div>
<p>
	<label><?php _e( 'Add a filter', 'tribe-events-calendar-pro' ); ?>:
		<select class="widefat calendar-widget-add-filter" id="<?php echo $this->get_field_id( 'selector' ); ?>" data-storage="<?php echo $this->get_field_id( 'filters' ); ?>">
			<?php
			echo "<option value='0'>" . __( 'Select one...', 'tribe-events-calendar-pro' ) . "</option>";
			foreach ( $taxonomies as $tax ) {
				echo sprintf( "<optgroup id='%s' label='%s'>", $tax->name, $tax->labels->name );
				$terms = get_terms( $tax->name, array( 'hide_empty' => false ) );
				foreach ( $terms as $term ) {
					echo sprintf( "<option value='%d'>%s</option>", $term->term_id, $term->name );
				}
				echo "</optgroup>";
			}
			?>
		</select>
	</label>
</p>

<script type="text/javascript">

	jQuery(document).ready(function ($) {
		if ($('div.widgets-sortables').find('select.calendar-widget-add-filter:not(#widget-tribe-mini-calendar-__i__-selector)').length && !$('#customize-controls').length) {
			$(".select2-container.calendar-widget-add-filter").remove();
			setTimeout(function () {
				$("select.calendar-widget-add-filter:not(#widget-tribe-mini-calendar-__i__-selector)").select2();
				calendar_toggle_all();
			}, 600);
		}
	});
</script>
