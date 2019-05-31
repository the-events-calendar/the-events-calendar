<?php
$keywords                = new stdClass;
$keywords->placeholder   = __( 'Keyword(s)', 'the-events-calendar' );
$location                = new stdClass;
$location->placeholder   = __( 'Location', 'the-events-calendar' );
$start_date              = new stdClass;
$start_date->placeholder = __( 'Date', 'the-events-calendar' );
$radius                  = new stdClass;
$radius->placeholder     = sprintf( _x( 'Radius (%s)', 'Radius with abbreviation', 'the-events-calendar' ), Tribe__Events__Utils__Radius::get_abbreviation() );
$depends_condition       = 'data-condition-not-empty';

switch ( $origin_slug ) {
	case 'ics':
		$depends = "#tribe-ea-field-{$origin_slug}_file";
		$radius->help = __( 'Use the filters to narrow down which events are fetched from your ICS file.', 'the-events-calendar' );
		break;
	case 'gcal':
		$depends = "#tribe-ea-field-{$origin_slug}_import_type";
		$radius->help = __( 'Use the filters to narrow down which events are fetched from this Google Calendar.', 'the-events-calendar' );
		break;
	case 'url':
		$depends = "#tribe-ea-field-{$origin_slug}_import_type";
		$radius->help = __( 'Use the filters to narrow down which events are fetched from this site.', 'the-events-calendar' );
		break;
	case 'eventbrite':
		$depends = "#tribe-ea-field-{$origin_slug}_import_source";
		$depends_condition = 'data-condition=source_type_url';
		$radius->help = __( 'Use the filters to narrow down which events are fetched from Eventbrite.', 'the-events-calendar' );
		// Only new events
		if ( empty( $record->meta['start'] ) ) {
			$record->meta['start'] = date_i18n( 'Y-m-d' );
		}

		break;
	case 'ical':
	default:
		$depends = "#tribe-ea-field-{$origin_slug}_import_type";
		$radius->help = __( 'Use the filters to narrow down which events are fetched from this iCalendar feed.', 'the-events-calendar' );
		break;
}

/**
 * Allow filtering of origins excluded from refining EA results by keyword.
 *
 * @since 4.6.24
 *
 * @param array $keyword_exclusions List of origins excluded.
 */
$keyword_exclusions = apply_filters( 'tribe_events_aggregator_refine_keyword_exclusions', array( 'facebook' ) );
$keyword_exclusions = json_encode( $keyword_exclusions );

/**
 * Allow filtering of origins excluded from refining EA results by location.
 *
 * @since 4.6.24
 *
 * @param array $location_exclusions List of origins excluded.
 */
$location_exclusions = apply_filters( 'tribe_events_aggregator_refine_location_exclusions', array( 'url', 'facebook' ) );
$location_exclusions = json_encode( $location_exclusions );
?>
<tr class="tribe-dependent tribe-refine-filters <?php echo esc_attr( $origin_slug ) ?>" data-depends="<?php echo esc_attr( $depends ); ?>" <?php echo esc_attr( $depends_condition ); ?>>
	<th scope="row">
		<label for="tribe-ea-field-refine_keywords"><?php echo __( 'Refine:', 'the-events-calendar' ); ?></label>
	</th>
	<td>
		<div class="tribe-refine tribe-dependent" data-depends="#tribe-ea-field-origin" data-condition-not="<?php echo esc_attr( $keyword_exclusions ); ?>">
			<input
				name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][keywords]"
				type="text"
				id="tribe-ea-field-<?php echo esc_attr( $origin_slug ); ?>_keywords"
				class="tribe-ea-field tribe-ea-size-xlarge"
				placeholder="<?php echo esc_attr( $keywords->placeholder ); ?>"
				value="<?php echo esc_attr( empty( $record->meta['keywords'] ) ? '' : $record->meta['keywords'] ); ?>"
			>
			<span class="tribe-bumpdown-trigger tribe-bumpdown-permanent tribe-bumpdown-nohover tribe-ea-help dashicons dashicons-editor-help" data-bumpdown="<?php echo esc_attr( $radius->help ); ?>" data-width-rule="all-triggers"></span>
		</div>
		<div class="tribe-refine">
			<?php
				$start = empty( $record->meta['start'] ) ? '' : $record->meta['start'];
				if ( is_numeric( $start ) ) {
					$start = date( Tribe__Date_Utils::DATEONLYFORMAT, $start );
				}
			?>
			<input
				name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][start]"
				type="text"
				id="tribe-ea-field-<?php echo esc_attr( $origin_slug ); ?>_start"
				class="tribe-ea-field tribe-ea-size-medium tribe-datepicker"
				placeholder="<?php echo esc_attr( $start_date->placeholder ); ?>"
				value="<?php echo esc_attr( $start ); ?>"
				<?php if ( 'eventbrite' === $origin_slug ) : ?>
					data-validation-is-required
					data-validation-error="<?php esc_attr_e( 'Start date for Eventbrite Tickets is Required', 'the-events-calendar' ); ?>"
				<?php endif; ?>
			>
			<span
				class="tribe-dependent tribe-date-helper"
				data-depends="#tribe-ea-field-<?php echo esc_attr( $origin_slug ); ?>_start"
				data-condition-not-empty
			>
				<?php esc_html_e( 'Events on or after', 'the-events-calendar' ); ?>
				<span id="tribe-date-helper-date-<?php echo esc_attr( $origin_slug ); ?>"><?php echo esc_html( $start ); ?></span>
			</span>
		</div>
		<div class="tribe-refine tribe-dependent" data-depends="#tribe-ea-field-origin"
		     data-condition-relation="and" data-condition-not="<?php echo esc_attr( $location_exclusions ); ?>">
			<input
				name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][location]"
				type="text"
				id="tribe-ea-field-<?php echo esc_attr( $origin_slug ); ?>_location"
				class="tribe-ea-field tribe-ea-size-large"
				placeholder="<?php echo esc_attr( $location->placeholder ); ?>"
				value="<?php echo esc_attr( empty( $record->meta['location'] ) ? '' : $record->meta['location'] ); ?>"
			>
			<select
				name="aggregator[<?php echo esc_attr( $origin_slug ); ?>][radius]"
				id="tribe-ea-field-<?php echo esc_attr( $origin_slug ); ?>_radius"
				class="tribe-ea-field tribe-ea-dropdown tribe-ea-size-medium"
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
		</div>
	</td>
</tr>
