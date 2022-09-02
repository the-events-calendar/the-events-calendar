/**
 * External dependencies
 */
import React, { Fragment, ReactElement } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	TimeZone,
} from '@moderntribe/events/elements';
import {
	date,
	moment as momentUtil,
} from '@moderntribe/common/utils';
import { settings, wpHooks } from '@moderntribe/common/utils/globals';
import HumanReadableInput from '../human-readable-input/container';

/**
 * Module Code
 */

const { FORMATS, TODAY } = date;
const {
	toMoment,
	toDate,
	toDateNoYear,
	toTime,
	isSameYear,
} = momentUtil;
FORMATS.date = settings() && settings().dateWithYearFormat
	? settings().dateWithYearFormat
	: __( 'F j', 'the-events-calendar' );

/**
 * Renders a separator based on the type called
 *
 * @param {object} props The props passed to the template
 * @param {string} type - The type of separator
 * @param {Array|string} className The class names for the separator
 * @returns {ReactElement} A React Dom Element null if none.
 */
const renderSeparator = ( props, type, className ) => {
	const { separatorDate, separatorTime } = props;

	switch ( type ) {
		case 'date-time':
			return (
				<span className={ classNames( 'tribe-editor__separator', className ) }>
					{ ` ${ separatorDate } ` }
				</span>
			);
		case 'time-range':
			return (
				<span className={ classNames( 'tribe-editor__separator', className ) }>
					{ ` ${ separatorTime } ` }
				</span>
			);
		case 'all-day':
			return (
				<span className={ classNames( 'tribe-editor__separator', className ) }>
					{ __( 'All Day', 'the-events-calendar' ) }
				</span>
			);
		default:
			return null;
	}
};

const renderStartDate = ( { start, end } ) => {
	let startDate = toDate( toMoment( start ) );

	if ( isSameYear( start, end ) && isSameYear( start, TODAY ) ) {
		startDate = toDateNoYear( toMoment( start ) );
	}

	return (
		<span className="tribe-editor__subtitle__headline-date">{ startDate }</span>
	);
};

const renderStartTime = ( props ) => {
	const { start, allDay } = props;

	if ( allDay ) {
		return null;
	}

	return (
		<Fragment>
			{ renderSeparator( props, 'date-time' ) }
			{ toTime( toMoment( start ), FORMATS.WP.time ) }
		</Fragment>
	);
};

const renderEndDate = ( { start, end, multiDay } ) => {
	if ( ! multiDay ) {
		return null;
	}

	let endDate = toDate( toMoment( end ) );

	if ( isSameYear( start, end ) && isSameYear( start, TODAY ) ) {
		endDate = toDateNoYear( toMoment( end ) );
	}

	return (
		<span className="tribe-editor__subtitle__headline-date">{ endDate }</span>
	);
};

const renderEndTime = ( props ) => {
	const { end, multiDay, allDay, sameStartEnd } = props;

	if ( allDay || sameStartEnd ) {
		return null;
	}

	return (
		<Fragment>
			{ multiDay && renderSeparator( props, 'date-time' ) }
			{ toTime( toMoment( end ), FORMATS.WP.time ) }
		</Fragment>
	);
};

const renderTimezone = ( props ) => {
	const { attributes, setAttributes } = props;
	const { timeZoneLabel, showTimeZone } = attributes;

	const setTimeZoneLabel = label => setAttributes( { timeZoneLabel: label } );

	return showTimeZone && (
		<span
			key="time-zone"
			className="tribe-editor__time-zone"
		>
			<TimeZone
				value={ timeZoneLabel }
				placeholder={ timeZoneLabel }
				onChange={ setTimeZoneLabel }
			/>
		</span>
	);
};

const renderExtras = ( props ) => (
	<Fragment>
		{ renderTimezone( props ) }
	</Fragment>
);

const renderContentHook = ( props ) => (
	wpHooks.applyFilters( 'blocks.eventDatetime.contentHook', null, props )
);

const EventDateTimeContent = ( props ) => {
	const {
		multiDay,
		allDay,
		sameStartEnd,
		isEditable,
		setAttributes,
		isOpen,
		open,
	} = props;

	return (
		isOpen && isEditable
			? <HumanReadableInput
				after={ renderExtras( props ) }
				setAttributes={ setAttributes }
			/>
			: (
				<Fragment>
					<h2 className="tribe-editor__subtitle__headline">
						<div className="tribe-editor__subtitle__headline-content">
							<button
								className="tribe-editor__btn--label tribe-editor__subtitle__headline-button"
								onClick={ open }
								disabled={ ! isEditable }
							>
								{ renderStartDate( props ) }
								{ renderStartTime( props ) }
								{
									( multiDay || ( ! allDay && ! sameStartEnd ) ) &&
									renderSeparator( props, 'time-range' )
								}
								{ renderEndDate( props ) }
								{ renderEndTime( props ) }
								{ allDay && renderSeparator( props, 'all-day' ) }
							</button>
							{ renderExtras( props ) }
						</div>
					</h2>
					{ renderContentHook( props ) }
				</Fragment>
			)
	);
};

EventDateTimeContent.propTypes = {
	allDay: PropTypes.bool,
	cost: PropTypes.string,
	currencyPosition: PropTypes.oneOf( [ 'prefix', 'suffix', '' ] ),
	currencySymbol: PropTypes.string,
	currencyCode: PropTypes.string,
	currencyCost: PropTypes.string,
	end: PropTypes.string,
	isEditable: PropTypes.bool,
	isOpen: PropTypes.bool,
	multiDay: PropTypes.bool,
	open: PropTypes.func,
	sameStartEnd: PropTypes.bool,
	separatorDate: PropTypes.string,
	separatorTime: PropTypes.string,
	setCost: PropTypes.func,
	start: PropTypes.string,
};

export default EventDateTimeContent;
