<?php
$keywords                = new stdClass;
$keywords->placeholder   = __( 'Add Keyword', 'the-events-calendar' );
$location                = new stdClass;
$location->placeholder   = __( 'Add Location', 'the-events-calendar' );
$start_date              = new stdClass;
$start_date->placeholder = __( 'Start Date', 'the-events-calendar' );
$radius                  = new stdClass;
$radius->placeholder     = sprintf( _x( 'Radius (%s)', 'Radius with abbreviation', 'the-events-calendar' ), Tribe__Events__Utils__Radius::get_abbreviation() );
$radius->help            = __( 'Use the filters to narrow down which events are fetched from your ICS file.', 'the-events-calendar' );

if ( 'ics' === $origin_slug ) {
	$depends = "#tribe-ea-field-{$origin_slug}_file";
} else {
	$depends = "#tribe-ea-field-{$origin_slug}_source";
}
?>
<tr class="tribe-dependent tribe-refine-filters" data-depends="<?php echo esc_attr( $depends ); ?>" data-condition-not-empty>
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
			value="<?php echo esc_attr( empty( $record->meta['keywords'] ) ? '' : $record->meta['keywords'] ); ?>"
		>
		<input
			name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][start]"
			type="text"
			id="tribe-ea-field-ics_start"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $start_date->placeholder ); ?>"
			value="<?php echo esc_attr( empty( $record->meta['start'] ) ? '' : $record->meta['start'] ); ?>"
		>
		<input
			name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][location]"
			type="text"
			id="tribe-ea-field-ics_location"
			class="tribe-ea-field tribe-ea-size-large"
			placeholder="<?php echo esc_attr( $location->placeholder ); ?>"
			value="<?php echo esc_attr( empty( $record->meta['location'] ) ? '' : $record->meta['location'] ); ?>"
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
				?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, empty( $record->meta['radius'] ) ? '' : $record->meta['radius'] ); ?>><?php esc_html_e( $name ); ?></option>
				<?php
			}
			?>
		</select>
		<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $radius->help ); ?>"></span>
	</td>
</tr>
