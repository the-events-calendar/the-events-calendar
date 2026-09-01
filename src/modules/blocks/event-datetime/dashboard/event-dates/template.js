/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';
import './style.pcss';

const { tec } = globals;

const getConfig = () => tec().recurrenceDates || {};

const parseRows = ( dates ) => {
	try {
		const rows = JSON.parse( dates || '[]' );
		return Array.isArray( rows ) ? rows : [];
	} catch ( error ) {
		return [];
	}
};

const toShortTime = ( value ) => ( value || '' ).substring( 0, 5 );

const toStoredTime = ( value ) => ( value && value.length === 5 ? `${ value }:00` : value );

/**
 * The Event Dates panel: authors the additional, explicit dates of an Event.
 *
 * The rows bind to the `dates` block attribute, a JSON mirror of the authored
 * dates; the server consumes it into the canonical meta on save.
 *
 * @since TBD
 *
 * @param {Object} props The datetime block dashboard props.
 *
 * @return {JSX.Element} The Event Dates panel.
 */
const EventDates = ( props ) => {
	const { attributes = {}, setAttributes } = props;
	const config = getConfig();
	const rows = parseRows( attributes.dates );

	const updateRows = ( nextRows ) => setAttributes( { dates: JSON.stringify( nextRows ) } );

	const updateRow = ( index, field, value ) => {
		const nextRows = rows.map( ( row, i ) => ( i === index ? { ...row, [ field ]: value } : row ) );
		updateRows( nextRows );
	};

	if ( config.isOccurrence ) {
		return (
			<div className="tribe-editor__event-dates">
				<p className="tribe-editor__event-dates__notice">
					{ __( 'This is a single occurrence.', 'the-events-calendar' ) }{ ' ' }
					{ config.parentEditLink && (
						<a href={ config.parentEditLink }>
							{ __( 'Edit the recurring event to change its dates.', 'the-events-calendar' ) }
						</a>
					) }
				</p>
			</div>
		);
	}

	if ( config.locked ) {
		const summary = config.summary || {};
		const nextDates = Array.isArray( summary.nextDates ) ? summary.nextDates : [];

		return (
			<div className="tribe-editor__event-dates">
				<p className="tribe-editor__event-dates__notice">
					{ __(
						'This event uses recurrence rules created with Events Calendar Pro. Activate Events Calendar Pro to edit them; the existing dates are preserved meanwhile.',
						'the-events-calendar'
					) }
				</p>
				{ summary.count > 0 && (
					<p className="tribe-editor__event-dates__summary">
						{ sprintf(
							/* translators: %d: the number of scheduled dates of the event. */
							_n( '%d date is scheduled:', '%d dates are scheduled:', summary.count, 'the-events-calendar' ),
							summary.count
						) }{ ' ' }
						{ nextDates.join( ', ' ) }
						{ summary.count > nextDates.length ? ', …' : '' }
					</p>
				) }
			</div>
		);
	}

	return (
		<div className="tribe-editor__event-dates">
			<h3 className="tribe-editor__event-dates__title">{ __( 'Event Dates', 'the-events-calendar' ) }</h3>
			<p className="tribe-editor__event-dates__description">
				{ __(
					'Add more dates to this event, one by one. Each date becomes its own entry on the calendar, with its own link.',
					'the-events-calendar'
				) }
			</p>
			{ rows.map( ( row, index ) => (
				<div className="tribe-editor__event-dates__row" key={ index }>
					<span className="tribe-editor__event-dates__row-label">{ __( 'On', 'the-events-calendar' ) }</span>
					<input
						type="date"
						value={ row.date || '' }
						onChange={ ( event ) => updateRow( index, 'date', event.target.value ) }
					/>
					<span className="tribe-editor__event-dates__row-label">
						{ __( 'from', 'the-events-calendar' ) }
					</span>
					<input
						type="time"
						value={ toShortTime( row.start ) }
						onChange={ ( event ) => updateRow( index, 'start', toStoredTime( event.target.value ) ) }
					/>
					<span className="tribe-editor__event-dates__row-label">{ __( 'to', 'the-events-calendar' ) }</span>
					<input
						type="time"
						value={ toShortTime( row.end ) }
						onChange={ ( event ) => updateRow( index, 'end', toStoredTime( event.target.value ) ) }
					/>
					<button
						type="button"
						className="tribe-editor__event-dates__remove"
						onClick={ () => updateRows( rows.filter( ( unused, i ) => i !== index ) ) }
					>
						{ __( 'Remove', 'the-events-calendar' ) }
					</button>
				</div>
			) ) }
			<button
				type="button"
				className="button tribe-editor__event-dates__add"
				onClick={ () => updateRows( [ ...rows, { date: '', start: '08:00:00', end: '17:00:00' } ] ) }
			>
				{ __( 'Add another date', 'the-events-calendar' ) }
			</button>
		</div>
	);
};

EventDates.propTypes = {
	attributes: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default EventDates;
