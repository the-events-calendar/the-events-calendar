/**
 * External dependencies
 */
import React, { Fragment, useRef, useState } from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';

/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TimePicker } from '@moderntribe/common/elements';
import { date as dateUtil, moment as momentUtil, time as timeUtil, globals } from '@moderntribe/common/utils';
import './style.pcss';

const { tec } = globals;
const { FORMATS } = dateUtil;

const getConfig = () => tec().recurrenceDates || {};

const parseRows = ( dates ) => {
	try {
		const rows = JSON.parse( dates || '[]' );
		return Array.isArray( rows ) ? rows : [];
	} catch ( error ) {
		return [];
	}
};

const defaultRow = () => ( { date: '', start: '08:00:00', end: '17:00:00' } );

/**
 * The Event Dates panel: authors the additional, explicit dates of an Event.
 *
 * Hidden behind a toggle, mirroring the Classic Editor section; the rows reuse the
 * dashboard's own TimePicker controls. The rows bind to the `dates` block attribute,
 * a JSON mirror of the authored dates; the server consumes it into the canonical
 * meta on save.
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

	const [ isOpen, setIsOpen ] = useState( rows.length > 0 );
	const [ editing, setEditing ] = useState( {} );
	const stash = useRef( [] );

	const timeFormat = FORMATS.WP.time;

	const updateRows = ( nextRows ) => setAttributes( { dates: JSON.stringify( nextRows ) } );

	const updateRow = ( index, field, value ) => {
		const nextRows = rows.map( ( row, i ) => ( i === index ? { ...row, [ field ]: value } : row ) );
		updateRows( nextRows );
	};

	const toDisplayTime = ( stored ) => {
		const parsed = moment( stored || '', 'HH:mm:ss' );
		return parsed.isValid() ? parsed.format( momentUtil.toFormat( timeFormat ) ) : '';
	};

	const onToggle = ( checked ) => {
		if ( checked ) {
			if ( ! rows.length ) {
				updateRows( stash.current.length ? stash.current : [ defaultRow() ] );
			}
		} else {
			// Kept around so toggling back on before saving restores the rows.
			stash.current = rows;
			updateRows( [] );
		}

		setIsOpen( checked );
	};

	const removeRow = ( index ) => {
		const nextRows = rows.filter( ( unused, i ) => i !== index );
		updateRows( nextRows );

		if ( ! nextRows.length ) {
			stash.current = [];
			setIsOpen( false );
		}
	};

	const renderTimePicker = ( index, field, row ) => {
		const key = `${ index }:${ field }`;
		const current = editing[ key ] !== undefined ? editing[ key ] : toDisplayTime( row[ field ] );

		return (
			<TimePicker
				current={ current }
				start={ timeUtil.START_OF_DAY }
				end={ timeUtil.END_OF_DAY }
				timeFormat={ timeFormat }
				onChange={ ( event ) => setEditing( { ...editing, [ key ]: event.target.value } ) }
				onBlur={ ( event ) => {
					const parsed = moment( event.target.value, [ momentUtil.TIME_FORMAT, 'HH:mm' ] );

					if ( parsed.isValid() ) {
						updateRow( index, field, parsed.format( 'HH:mm:ss' ) );
					}

					const nextEditing = { ...editing };
					delete nextEditing[ key ];
					setEditing( nextEditing );
				} }
				onClick={ ( value, onClose ) => {
					if ( 'all-day' !== value ) {
						updateRow( index, field, `${ timeUtil.fromSeconds( value, timeUtil.TIME_FORMAT_HH_MM ) }:00` );
					}

					onClose();
				} }
			/>
		);
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
			<ToggleControl
				label={ __( 'Schedule this event on more dates', 'the-events-calendar' ) }
				checked={ isOpen }
				onChange={ onToggle }
				__nextHasNoMarginBottom={ true }
			/>
			{ isOpen && (
				<Fragment>
					<p className="tribe-editor__event-dates__description">
						{ __(
							'Each date becomes its own entry on the calendar, with its own link. The event date above is always included.',
							'the-events-calendar'
						) }
					</p>
					{ rows.map( ( row, index ) => (
						<div className="tribe-editor__event-dates__row" key={ index }>
							<input
								type="date"
								className="tribe-editor__event-dates__date"
								value={ row.date || '' }
								onChange={ ( event ) => updateRow( index, 'date', event.target.value ) }
							/>
							{ renderTimePicker( index, 'start', row ) }
							<span className="tribe-editor__event-dates__separator">
								{ __( 'to', 'the-events-calendar' ) }
							</span>
							{ renderTimePicker( index, 'end', row ) }
							<button
								type="button"
								className="tribe-editor__event-dates__control tribe-editor__event-dates__remove"
								aria-label={ __( 'Remove this date', 'the-events-calendar' ) }
								onClick={ () => removeRow( index ) }
							>
								&minus;
							</button>
							{ index === rows.length - 1 && (
								<button
									type="button"
									className="tribe-editor__event-dates__control tribe-editor__event-dates__add"
									aria-label={ __( 'Add another date', 'the-events-calendar' ) }
									onClick={ () => updateRows( [ ...rows, defaultRow() ] ) }
								>
									+
								</button>
							) }
						</div>
					) ) }
				</Fragment>
			) }
		</div>
	);
};

EventDates.propTypes = {
	attributes: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default EventDates;
