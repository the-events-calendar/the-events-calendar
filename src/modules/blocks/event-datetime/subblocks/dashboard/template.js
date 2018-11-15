/**
 * External dependencies
 */
import React, { PureComponent, Fragment } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TimePicker } from '@moderntribe/common/elements';
import {
	Dashboard,
	Month,
	Upsell,
} from '@moderntribe/events/elements';
import {
	date,
	moment as momentUtil,
	time,
	globals,
} from '@moderntribe/common/utils';
import { DateTimePluginBlockHook } from '../../hooks';

/**
 * Module Code
 */

const { FORMATS, TODAY } = date;
const {
	roundTime,
	toMoment,
	toDate,
	toDateNoYear,
	isSameYear,
} = momentUtil;

FORMATS.date = globals.settings() && globals.settings().dateWithYearFormat
	? globals.settings().dateWithYearFormat
	: __( 'F j', 'events-gutenberg' );

export default class EventDateTimeDashboard extends PureComponent {
	static propTypes = {
		allDay: PropTypes.bool,
		end: PropTypes.string,
		isDashboardOpen: PropTypes.bool,
		multiDay: PropTypes.bool,
		onEndTimePickerChange: PropTypes.func,
		onEndTimePickerClick: PropTypes.func,
		onMultiDayToggleChange: PropTypes.func,
		onSelectDay: PropTypes.func,
		onStartTimePickerChange: PropTypes.func,
		onStartTimePickerClick: PropTypes.func,
		setVisibleMonth: PropTypes.func,
		start: PropTypes.string,
		visibleMonth: PropTypes.instanceOf( Date ),
	};

	get shouldHideUpsell() {
		return globals.editorConstants().hide_upsell === 'true';
	}

	renderStartTimePicker = () => {
		const {
			start,
			end,
			allDay,
			multiDay,
			onStartTimePickerChange,
			onStartTimePickerClick,
		} = this.props;
		const startMoment = toMoment( start );
		const endMoment = toMoment( end );

		const timePickerProps = {
			current: startMoment.format( 'HH:mm' ),
			start: time.START_OF_DAY,
			end: time.END_OF_DAY,
			onChange: onStartTimePickerChange,
			onClick: onStartTimePickerClick,
			timeFormat: FORMATS.WP.time,
			showAllDay: true,
			allDay,
		};

		if ( ! multiDay ) {
			const max = endMoment.clone().subtract( 1, 'minutes' );
			timePickerProps.end = roundTime( max ).format( 'HH:mm' );
			timePickerProps.max = max.format( 'HH:mm' );
		}

		let startDate = toDate( toMoment( start ) );
		if ( isSameYear( start, end ) && isSameYear( start, TODAY ) ) {
			startDate = toDateNoYear( toMoment( start ) );
		}

		return (
			<Fragment>
				<span className="tribe-editor__time-picker__label">{ startDate }</span>
				<TimePicker { ...timePickerProps } />
			</Fragment>
		);
	}

	renderMultiDayToggle = () => {
		const { multiDay, onMultiDayToggleChange } = this.props;

		return (
			<ToggleControl
				label={ __( 'Multi-Day', 'events-gutenberg' ) }
				checked={ multiDay }
				onChange={ onMultiDayToggleChange }
			/>
		);
	}

	renderEndTimePicker = () => {
		const {
			start,
			end,
			multiDay,
			allDay,
			onEndTimePickerChange,
			onEndTimePickerClick,
		} = this.props;

		if ( ! multiDay && allDay ) {
			return null;
		}

		const startMoment = toMoment( start );
		const endMoment = toMoment( end );

		const timePickerProps = {
			current: endMoment.format( 'HH:mm' ),
			start: time.START_OF_DAY,
			end: time.END_OF_DAY,
			onChange: onEndTimePickerChange,
			onClick: onEndTimePickerClick,
			timeFormat: FORMATS.WP.time,
			showAllDay: true,
			allDay,
		};

		if ( ! multiDay ) {
		// if the start time has less than half an hour left in the day
			if ( endMoment.clone().add( 1, 'days' ).startOf( 'day' ).diff( startMoment, 'seconds' ) <= time.HALF_HOUR_IN_SECONDS ) {
				timePickerProps.start = endMoment.clone().endOf( 'day' ).format( 'HH:mm' );
			} else {
				timePickerProps.start = roundTime( startMoment ).add( 30, 'minutes' ).format( 'HH:mm' );
			}
			timePickerProps.min = startMoment.clone().add( 1, 'minutes' ).format( 'HH:mm' );
		}

		let endDate = toDate( toMoment( end ) );
		if ( isSameYear( start, end ) && isSameYear( start, TODAY ) ) {
			endDate = toDateNoYear( toMoment( end ) );
		}

		return (
			<Fragment>
				{ multiDay && <span className="tribe-editor__time-picker__label">{ endDate }</span> }
				<TimePicker { ...timePickerProps } />
			</Fragment>
		);
	}

	renderCalendars = () => {
		const {
			multiDay,
			start,
			end,
			visibleMonth,
			setVisibleMonth,
			onSelectDay,
		} = this.props;

		const monthProps = {
			onSelectDay: onSelectDay,
			withRange: multiDay,
			from: toMoment( start ).toDate(),
			month: visibleMonth,
			setVisibleMonth,
		};

		if ( multiDay ) {
			monthProps.to = toMoment( end ).toDate();
		}

		return (
			<Month { ...monthProps } />
		);
	}

	render() {
		const { multiDay, allDay, isDashboardOpen } = this.props;

		return (
			<Dashboard isOpen={ isDashboardOpen }>
				<Fragment>
					<section className="tribe-editor__calendars">
						{ this.renderCalendars() }
					</section>
					<footer className="tribe-editor__subtitle__footer">
						<div className="tribe-editor__subtitle__footer-date">
							<div className="tribe-editor__subtitle__time-pickers">
								{ this.renderStartTimePicker() }
								{
									// TODO: Fix
									// ( multiDay || ! allDay ) && (
									// 	this.renderSeparator( 'time-range', 'tribe-editor__time-picker__separator' )
									// )
								}
								{ this.renderEndTimePicker() }
							</div>
							<div className="tribe-editor__subtitle__footer-multiday">
								{ this.renderMultiDayToggle() }
							</div>
						</div>
						<DateTimePluginBlockHook />
						{ ! this.shouldHideUpsell && <Upsell /> }
					</footer>
				</Fragment>
			</Dashboard>
		);
	}
}
