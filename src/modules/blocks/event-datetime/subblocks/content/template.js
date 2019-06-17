/**
 * External dependencies
 */
import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PlainText } from '@wordpress/editor';

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
import { editor, settings } from '@moderntribe/common/utils/globals';
import HumanReadableInput from '../../human-readable-input/container';
import ContentHook from './hook';
import Controls from '../../controls';

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

class EventDateTimeContent extends Component {
	static propTypes = {
		allDay: PropTypes.bool,
		cost: PropTypes.string,
		currencyPosition: PropTypes.oneOf( [ 'prefix', 'suffix', '' ] ),
		currencySymbol: PropTypes.string,
		end: PropTypes.string,
		isEditable: PropTypes.bool,
		multiDay: PropTypes.bool,
		onDateTimeLabelClick: PropTypes.func,
		separatorDate: PropTypes.string,
		separatorTime: PropTypes.string,
		setCost: PropTypes.func,
		setTimeZoneLabel: PropTypes.func,
		showDateInput: PropTypes.bool,
		showTimeZone: PropTypes.bool,
		start: PropTypes.string,
		timeZone: PropTypes.string,
		timeZoneLabel: PropTypes.string,
	};

	renderPrice = () => {
		const { cost, currencyPosition, currencySymbol, setCost } = this.props;

		// Bail when not classic
		if ( ! editor() || ! editor().isClassic ) {
			return null;
		}

		return (
			<div
				key="tribe-editor-event-cost"
				className="tribe-editor__event-cost"
			>
				{ 'prefix' === currencyPosition && <span>{ currencySymbol }</span> }
				<PlainText
					className={ classNames( 'tribe-editor__event-cost__value', `tribe-editor-cost-symbol-position-${ currencyPosition }` ) }
					value={ cost }
					placeholder={ __( 'Enter price', 'the-events-calendar' ) }
					onChange={ setCost }
				/>
				{ 'suffix' === currencyPosition && <span>{ currencySymbol }</span> }
			</div>
		);
	}

	renderStartDate = () => {
		const { start, end } = this.props;
		let startDate = toDate( toMoment( start ) );

		if ( isSameYear( start, end ) && isSameYear( start, TODAY ) ) {
			startDate = toDateNoYear( toMoment( start ) );
		}

		return (
			<span className="tribe-editor__subtitle__headline-date">{ startDate }</span>
		);
	}

	renderStartTime = () => {
		const { start, allDay } = this.props;

		if ( allDay ) {
			return null;
		}

		return (
			<Fragment>
				{ this.renderSeparator( 'date-time' ) }
				{ toTime( toMoment( start ), FORMATS.WP.time ) }
			</Fragment>
		);
	}

	renderEndDate = () => {
		const { start, end, multiDay } = this.props;

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
	}

	renderEndTime = () => {
		const { end, multiDay, allDay } = this.props;

		if ( allDay ) {
			return null;
		}

		return (
			<Fragment>
				{ multiDay && this.renderSeparator( 'date-time' ) }
				{ toTime( toMoment( end ), FORMATS.WP.time ) }
			</Fragment>
		);
	}

	renderTimezone = () => {
		const { setTimeZoneLabel, timeZoneLabel, showTimeZone } = this.props;

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
	}

	/**
	 * Renders a separator based on the type called
	 *
	 * @param {string} type - The type of separator
	 *
	 * @returns {ReactDOM} A React Dom Element null if none.
	 */
	renderSeparator = ( type, className ) => {
		const { separatorDate, separatorTime } = this.props;

		switch ( type ) {
			case 'date-time':
				return (
					<span className={ classNames( 'tribe-editor__separator', className ) }>
						{ ' '.concat( separatorDate, ' ' ) }
					</span>
				);
			case 'time-range':
				return (
					<span className={ classNames( 'tribe-editor__separator', className ) }>
						{ ' '.concat( separatorTime, ' ' ) }
					</span>
				);
			case 'all-day':
				return (
					<span className={ classNames( 'tribe-editor__separator', className ) }>{ __( 'All Day', 'the-events-calendar' ) }</span>
				);
			default:
				return null;
		}
	}

	renderExtras = () => {
		return (
			<Fragment>
				{ this.renderTimezone() }
				{ this.renderPrice() }
			</Fragment>
		);
	}

	/**
	 * Main label used to display the event
	 *
	 * @returns {ReactDOM} A React Dom Element null if none.
	 */
	render = () => {
		const {
			multiDay,
			allDay,
			showDateInput,
			onDateTimeLabelClick,
			isEditable,
		} = this.props;

		return (
			<Fragment>
				<Controls />
				{
					showDateInput && isEditable
						? <HumanReadableInput after={ this.renderExtras() } />
						: (
							<Fragment>
								<h2 className="tribe-editor__subtitle__headline">
									<div className="tribe-editor__subtitle__headline-content">
										<button
											className="tribe-editor__btn--label"
											onClick={ onDateTimeLabelClick }
											disabled={ ! isEditable }
										>
											{ this.renderStartDate() }
											{ this.renderStartTime() }
											{ ( multiDay || ! allDay ) && this.renderSeparator( 'time-range' ) }
											{ this.renderEndDate() }
											{ this.renderEndTime() }
											{ allDay && this.renderSeparator( 'all-day' ) }
										</button>
										{ this.renderExtras() }
									</div>
								</h2>
								<ContentHook />
							</Fragment>
						)
				}
			</Fragment>
		);
	}
}

export default EventDateTimeContent;
