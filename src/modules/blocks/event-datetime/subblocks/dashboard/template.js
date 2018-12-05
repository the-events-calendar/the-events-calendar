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
		separatorTime: PropTypes.string,
		setVisibleMonth: PropTypes.func,
		start: PropTypes.string,
		visibleMonth: PropTypes.instanceOf( Date ),
	};

	get shouldHideUpsell() {
		return globals.editorConstants().hideUpsell;
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
			separatorTime,
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

		return [
			<Controls />,
			(
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

			),
		];
	}
}
