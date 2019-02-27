/**
 * External dependencies
 */
import React, { PureComponent, Fragment } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

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
import DashboardHook from './hook';
import Controls from '../../controls';

/**
 * Module Code
 */

const { FORMATS, TODAY } = date;
const {
	toMoment,
	toDate,
	toDateNoYear,
	isSameYear,
} = momentUtil;

FORMATS.date = globals.settings() && globals.settings().dateWithYearFormat
	? globals.settings().dateWithYearFormat
	: __( 'F j', 'the-events-calendar' );

export default class EventDateTimeDashboard extends PureComponent {
	static propTypes = {
		allDay: PropTypes.bool,
		end: PropTypes.string,
		endTimeInput: PropTypes.string,
		isDashboardOpen: PropTypes.bool,
		multiDay: PropTypes.bool,
		onEndTimePickerBlur: PropTypes.func,
		onEndTimePickerChange: PropTypes.func,
		onEndTimePickerClick: PropTypes.func,
		onMultiDayToggleChange: PropTypes.func,
		onSelectDay: PropTypes.func,
		onStartTimePickerBlur: PropTypes.func,
		onStartTimePickerChange: PropTypes.func,
		onStartTimePickerClick: PropTypes.func,
		separatorTime: PropTypes.string,
		setVisibleMonth: PropTypes.func,
		start: PropTypes.string,
		startTimeInput: PropTypes.string,
		visibleMonth: PropTypes.instanceOf( Date ),
	};

	get shouldHideUpsell() {
		return globals.editorConstants().hideUpsell;
	}

	renderStartTimePicker = () => {
		const {
			start,
			end,
			startTimeInput,
			allDay,
			onStartTimePickerBlur,
			onStartTimePickerChange,
			onStartTimePickerClick,
		} = this.props;

		const timePickerProps = {
			current: startTimeInput,
			start: time.START_OF_DAY,
			end: time.END_OF_DAY,
			onBlur: onStartTimePickerBlur,
			onChange: onStartTimePickerChange,
			onClick: onStartTimePickerClick,
			timeFormat: FORMATS.WP.time,
			showAllDay: true,
			allDay,
		};

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
				label={ __( 'Multi-Day', 'the-events-calendar' ) }
				checked={ multiDay }
				onChange={ onMultiDayToggleChange }
			/>
		);
	}

	renderEndTimePicker = () => {
		const {
			start,
			end,
			endTimeInput,
			multiDay,
			allDay,
			onEndTimePickerBlur,
			onEndTimePickerChange,
			onEndTimePickerClick,
		} = this.props;

		if ( ! multiDay && allDay ) {
			return null;
		}

		const timePickerProps = {
			current: endTimeInput,
			start: time.START_OF_DAY,
			end: time.END_OF_DAY,
			onBlur: onEndTimePickerBlur,
			onChange: onEndTimePickerChange,
			onClick: onEndTimePickerClick,
			timeFormat: FORMATS.WP.time,
			showAllDay: true,
			allDay,
		};

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
		const { multiDay, allDay, separatorTime, isDashboardOpen } = this.props;

		return (
			<Fragment>
				<Controls />
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
										( multiDay || ! allDay ) && (
											<span className={ classNames( 'tribe-editor__separator', 'tribe-editor__time-picker__separator' ) }>
												{ ' '.concat( separatorTime, ' ' ) }
											</span>
										)
									}
									{ this.renderEndTimePicker() }
								</div>
								<div className="tribe-editor__subtitle__footer-multiday">
									{ this.renderMultiDayToggle() }
								</div>
							</div>
							<DashboardHook />
							{ ! this.shouldHideUpsell && <Upsell /> }
						</footer>
					</Fragment>
				</Dashboard>
			</Fragment>
		);
	}
}
