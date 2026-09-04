/**
 * External dependencies
 */
import React, { Fragment, useRef, useState } from 'react';
import PropTypes from 'prop-types';
import moment from 'moment';

/**
 * WordPress dependencies
 */
import { Button, ToggleControl, Tooltip } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TimePicker } from '@moderntribe/common/elements';
import { date as dateUtil, moment as momentUtil, time as timeUtil, globals } from '@moderntribe/common/utils';
import { Minus, Pencil, Plus } from '@moderntribe/events/icons';
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

const ALL_DAY_START = '00:00:00';
// The authored meta stores times without seconds: match all-day ends on HH:mm.
const isAllDayRow = ( row ) => ALL_DAY_START === row.start && 0 === ( row.end || '' ).indexOf( '23:59' );

/**
 * Builds a new row, one day after the last authored date (or the event's own date).
 *
 * @param {Array}  rows  The current rows.
 * @param {string} start The event start datetime, from the dashboard props.
 *
 * @return {Object} The new row.
 */
const defaultRow = ( rows = [], start = '' ) => {
	const base = rows.length ? rows[ rows.length - 1 ].date : ( start || '' ).substring( 0, 10 );
	const parsed = moment( base, 'YYYY-MM-DD' );

	return {
		date: parsed.isValid() ? parsed.add( 1, 'day' ).format( 'YYYY-MM-DD' ) : '',
		start: '08:00:00',
		end: '17:00:00',
	};
};

const chipClassName = ( status ) => `tribe-editor__event-dates__chip tribe-editor__event-dates__chip--${ status }`;

/**
 * One scheduled date of a rule-locked Event: a chip linking to the Occurrence, with a tooltip.
 *
 * @since TBD
 *
 * @param {Object} props      The component props.
 * @param {Object} props.chip The chip data: `label`, `tooltip` lines, `permalink` and `status`.
 *
 * @return {JSX.Element} The chip.
 */
const DateChip = ( { chip } ) => {
	const tooltip = Array.isArray( chip.tooltip ) ? chip.tooltip.join( ' · ' ) : '';

	return (
		<li>
			<span className="tribe-editor__event-dates__chip-group">
				<Tooltip text={ tooltip } position="top center">
					{ chip.permalink ? (
						<a
							className={ chipClassName( chip.status ) }
							href={ chip.permalink }
							target="_blank"
							rel="noreferrer noopener"
						>
							{ chip.label }
						</a>
					) : (
						<span className={ chipClassName( chip.status ) } tabIndex={ 0 }>
							{ chip.label }
						</span>
					) }
				</Tooltip>
				{ chip.editLink && (
					<Button
						className="tribe-editor__event-dates__chip-edit"
						href={ chip.editLink }
						icon={ <Pencil /> }
						label={ sprintf(
							/* translators: %s: the date of the occurrence. */
							__( 'Edit the occurrence on %s (opens in a new tab)', 'the-events-calendar' ),
							chip.label
						) }
						size="small"
						target="_blank"
						rel="noreferrer noopener"
					/>
				) }
			</span>
		</li>
	);
};

DateChip.propTypes = {
	chip: PropTypes.shape( {
		label: PropTypes.string,
		tooltip: PropTypes.arrayOf( PropTypes.string ),
		permalink: PropTypes.string,
		editLink: PropTypes.string,
		status: PropTypes.string,
	} ).isRequired,
};

/**
 * The scheduled dates of a rule-locked Event as chips: the upcoming ones always
 * visible, the past ones collapsed behind a toggle.
 *
 * @since TBD
 *
 * @param {Object} props         The component props.
 * @param {Object} props.summary The `recurrenceDates.summary` editor config: `count` and `dates`.
 *
 * @return {JSX.Element|null} The chips, or nothing when no date is scheduled.
 */
const LockedDates = ( { summary } ) => {
	const [ showPast, setShowPast ] = useState( false );
	const dates = Array.isArray( summary.dates ) ? summary.dates : [];

	if ( ! dates.length ) {
		return null;
	}

	const upcoming = dates.filter( ( chip ) => 'past' !== chip.status );
	const past = dates.filter( ( chip ) => 'past' === chip.status );
	const toggleLabel = showPast
		? /* translators: %d: the number of past scheduled dates of the event. */
		  _n( 'Hide %d past date', 'Hide %d past dates', past.length, 'the-events-calendar' )
		: /* translators: %d: the number of past scheduled dates of the event. */
		  _n( 'Show %d past date', 'Show %d past dates', past.length, 'the-events-calendar' );

	return (
		<Fragment>
			<p className="tribe-editor__event-dates__summary">
				{ sprintf(
					/* translators: %d: the number of scheduled dates of the event. */
					_n( '%d date is scheduled.', '%d dates are scheduled.', dates.length, 'the-events-calendar' ),
					dates.length
				) }
			</p>
			{ upcoming.length > 0 && (
				<ul
					className="tribe-editor__event-dates__chips"
					aria-label={ __( 'Upcoming dates', 'the-events-calendar' ) }
				>
					{ upcoming.map( ( chip, index ) => (
						<DateChip key={ `upcoming-${ index }` } chip={ chip } />
					) ) }
				</ul>
			) }
			{ past.length > 0 && (
				<Fragment>
					<Button
						variant="link"
						className="tribe-editor__event-dates__toggle"
						aria-expanded={ showPast }
						onClick={ () => setShowPast( ! showPast ) }
					>
						{ sprintf( toggleLabel, past.length ) }
					</Button>
					{ showPast && (
						<ul
							className="tribe-editor__event-dates__chips tribe-editor__event-dates__chips--past"
							aria-label={ __( 'Past dates', 'the-events-calendar' ) }
						>
							{ past.map( ( chip, index ) => (
								<DateChip key={ `past-${ index }` } chip={ chip } />
							) ) }
						</ul>
					) }
				</Fragment>
			) }
		</Fragment>
	);
};

LockedDates.propTypes = {
	summary: PropTypes.shape( {
		count: PropTypes.number,
		dates: PropTypes.array,
	} ).isRequired,
};

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
	const { attributes = {}, setAttributes, start } = props;
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
				updateRows( stash.current.length ? stash.current : [ defaultRow( [], start ) ] );
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
		// The in-progress time text is keyed by row index: it would show up on the wrong row after a removal.
		setEditing( {} );

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
				allDay={ isAllDayRow( row ) }
				showAllDay={ true }
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
					if ( 'all-day' === value ) {
						// Both ends move together: the whole date becomes all-day.
						updateRows(
							rows.map( ( r, i ) =>
								i === index ? { ...r, start: ALL_DAY_START, end: '23:59:59' } : r
							)
						);
					} else {
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
					{ __(
						'This is a single occurrence: changing the dates above moves only this date.',
						'the-events-calendar'
					) }{ ' ' }
					{ config.parentEditLink && (
						<a href={ config.parentEditLink }>
							{ __( 'Edit the recurring event to change the other dates.', 'the-events-calendar' ) }
						</a>
					) }
				</p>
			</div>
		);
	}

	if ( config.locked ) {
		return (
			<div className="tribe-editor__event-dates">
				<p className="tribe-editor__event-dates__notice">
					{ __(
						'This event uses recurrence rules created with Events Calendar Pro. Activate Events Calendar Pro to edit them; the existing dates are preserved meanwhile.',
						'the-events-calendar'
					) }
				</p>
				<LockedDates summary={ config.summary || {} } />
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
								<Minus />
							</button>
							{ index === rows.length - 1 && (
								<button
									type="button"
									className="tribe-editor__event-dates__control tribe-editor__event-dates__add"
									aria-label={ __( 'Add another date', 'the-events-calendar' ) }
									onClick={ () => updateRows( [ ...rows, defaultRow( rows, start ) ] ) }
								>
									<Plus />
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
