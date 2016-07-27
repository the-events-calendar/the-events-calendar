<?php
$keywords                = new stdClass;
$keywords->placeholder   = __( 'Add Keyword', 'the-events-calendar' );
$location                = new stdClass;
$location->placeholder   = __( 'Add Location', 'the-events-calendar' );
$start_date              = new stdClass;
$start_date->placeholder = __( 'Start Date', 'the-events-calendar' );
$radius                  = new stdClass;
$radius->placeholder     = sprintf( _x( 'Radius (%s)', 'Radius with abbreviation', 'the-events-calendar' ), Tribe__Events__Utils__Radius::get_abbreviation() );

if ( 'ics' === $origin_slug ) {
	$depends = "#tribe-ea-field-{$origin_slug}_file";
} else {
	$depends = "#tribe-ea-field-{$origin_slug}_source";
}
?>
<tr class="tribe-dependent" data-depends="<?php echo esc_attr( $depends ); ?>" data-condition-not-empty>
	<th scope="row">
		<label for="tribe-ea-field-refine_keywords"><?php echo __( 'Refine:', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<input
			name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][keywords]"
			type="text"
			id="tribe-ea-field-ics_keywords"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $keywords->placeholder ); ?>"
		>
		<input
			name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][location]"
			type="text"
			id="tribe-ea-field-ics_location"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $location->placeholder ); ?>"
		>
		<input
			name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][start_date]"
			type="text"
			id="tribe-ea-field-ics_start_date"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $start_date->placeholder ); ?>"
		>
		<select
			name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][radius]"
			id="tribe-ea-field-ics_radius"
			class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $radius->placeholder ); ?>"
			data-hide-search
		>
			<option value=""></option>
			<?php
			foreach ( Tribe__Events__Utils__Radius::get_radii() as $name => $value ) {
				echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $name ) . '</option>';
			}
			?>
		</select>
	</td>
</tr>
