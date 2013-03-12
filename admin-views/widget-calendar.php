<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','tribe-events-calendar-pro' ); ?>
		<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>" />
	</label>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( '# of events to show in the list:', 'tribe-events-calendar-pro' ); ?>
		<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'count' ); ?>"
		       id="<?php echo $this->get_field_id( 'count' ); ?>"
		       value="<?php echo esc_attr( strip_tags( $instance['count'] ) ); ?>"/>
	</label>
</p>

<hr/>

<?php
$class = "";
if ( empty( $instance['filters'] ) ) {
	$class = "display:none;";
}
?>

<div class="calendar-widget-filters-container"  style="<?php echo $class;?>">

	<h3 class="calendar-widget-filters-title">Filters:</h3>

	<input type="hidden" name="<?php echo $this->get_field_name( 'filters' ); ?>"
	       id="<?php echo $this->get_field_id( 'filters' ); ?>" class="calendar-widget-added-filters"
	       value='<?php echo maybe_serialize( $instance['filters'] ); ?>'/>

	<div class="calendar-widget-filter-list">

		<?php
		if ( !empty( $instance['filters'] ) ) {

			foreach ( json_decode( $instance['filters'] ) as $tax => $terms ) {
				$tax_obj = get_taxonomy( $tax );

				foreach ( $terms as $term ) {
					if ( empty( $term ) )
						continue;
					$term_obj = get_term( $term, $tax );
					echo sprintf( "<li><p>%s: %s&nbsp;&nbsp;<span><a href='#' class='calendar-widget-remove-filter' data-tax='%s' data-term='%s'>(remove)</a></span></p></li>", $tax_obj->labels->name, $term_obj->name, $tax, $term_obj->term_id );
				}
			}
		}
		?>

	</div>

	<p class="calendar-widget-filters-operand">
		<label for="<?php echo $this->get_field_name( 'operand' ); ?>">
			<input <?php checked( $instance['operand'], 'AND' ); ?> type="radio"
			                                                        name="<?php echo $this->get_field_name( 'operand' ); ?>"
			                                                        value="AND">
			Match all</label><br/>
		<label for="<?php echo $this->get_field_name( 'operand' ); ?>">
			<input <?php checked( $instance['operand'], 'OR' ); ?> type="radio"
			                                                       name="<?php echo $this->get_field_name( 'operand' ); ?>"
			                                                       value="OR">
			Match any</label>
	</p>
</div>
<p>
	<label>Add a filter:
		<select class="widefat calendar-widget-add-filter" id="<?php echo $this->get_field_id( 'selector' ); ?>">
			<?php
			echo sprintf( "<option value='0'>Select one...</option>" );
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

<?php if ( !empty( $instance['filters'] ) ) { ?>
<script type="text/javascript">
	calendar_filters = <?php echo maybe_serialize( $instance['filters'] ); ?>;
	calendar_toggle( jQuery( '.calendar-widget-filters-operand' ).last().parents( '.widget-content' ) );	
	jQuery(document).ready(function($){
		if( jQuery('div.widgets-sortables').find('.calendar-widget-add-filter').length ) {
			jQuery( ".select2-container.calendar-widget-add-filter" ).remove();
			setTimeout(function(){  jQuery( ".calendar-widget-add-filter" ).select2(); calendar_toggle(); }, 600);
		}
	});		
</script>
<?php } ?>