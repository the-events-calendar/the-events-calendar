/**
 * External dependencies
 */
import React, { Fragment, useContext, PureComponent } from 'react';
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
import DateTimeContext from '../../context';

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

const shouldHideUpsell = () => {
	return globals.editorConstants().hideUpsell;
};

const renderStartTimePicker = ( {
	start,
	end,
	startTimeInput,
	allDay,
	onStartTimePickerBlur,
	onStartTimePickerChange,
	onStartTimePickerClick,
} ) => {
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
};

const renderMultiDayToggle = ( { multiDay, onMultiDayToggleChange } ) => {
	return (
		<ToggleControl
			label={ __( 'Multi-Day', 'the-events-calendar' ) }
			checked={ multiDay }
			onChange={ onMultiDayToggleChange }
		/>
	);
};

const renderEndTimePicker = ( {
	start,
	end,
	endTimeInput,
	multiDay,
	allDay,
	onEndTimePickerBlur,
	onEndTimePickerChange,
	onEndTimePickerClick,
} ) => {
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
};

class Calendars extends PureComponent {
	static propTypes = {
		end: PropTypes.string,
		multiDay: PropTypes.bool,
		onSelectDay: PropTypes.func,
		start: PropTypes.string,
	}

	constructor( props ) {
		super( props );
		this.state = { visibleMonth: toMoment( props.start ).toDate() };
	}

	setVisibleMonth = ( visibleMonth ) => {
		this.setState( { visibleMonth } );
	}

	render() {
		const { start, end, multiDay, onSelectDay } = this.props;

		const monthProps = {
			onSelectDay: onSelectDay,
			withRange: multiDay,
			from: toMoment( start ).toDate(),
			month: this.state.visibleMonth,
			setVisibleMonth: this.setVisibleMonth,
		};

		if ( multiDay ) {
			monthProps.to = toMoment( end ).toDate();
		}

		return <Month { ...monthProps } />;
	}
}

const EventDateTimeDashboard = ( props ) => {
	const { multiDay, allDay, separatorTime } = props;

	const {
		isOpen,
		showTimeZone,
		setShowTimeZone,
		setDateTimeAttributes,
	} = useContext(DateTimeContext);

	const controlProps = {
		showTimeZone,
		setShowTimeZone,
		setDateTimeAttributes,
	};

	return (
		<Fragment>
			<Controls { ...controlProps } />
			<Dashboard isOpen={ isOpen }>
				<Fragment>
					<section className="tribe-editor__calendars">
						<Calendars { ...props } />
					</section>
					<footer className="tribe-editor__subtitle__footer">
						<div className="tribe-editor__subtitle__footer-date">
							<div className="tribe-editor__subtitle__time-pickers">
								{ renderStartTimePicker( props ) }
								{
									( multiDay || ! allDay ) && (
										<span className={ classNames( 'tribe-editor__separator', 'tribe-editor__time-picker__separator' ) }>
											{ ' '.concat( separatorTime, ' ' ) }
										</span>
									)
								}
								{ renderEndTimePicker( props ) }
							</div>
							<div className="tribe-editor__subtitle__footer-multiday">
								{ renderMultiDayToggle( props ) }
							</div>
						</div>
						<DashboardHook />
						{ ! shouldHideUpsell() && <Upsell /> }
					</footer>
				</Fragment>
			</Dashboard>
		</Fragment>
	);
};

EventDateTimeDashboard.propTypes = {
	allDay: PropTypes.bool,
	end: PropTypes.string,
	endTimeInput: PropTypes.string,
	isOpen: PropTypes.bool,
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
	start: PropTypes.string,
	startTimeInput: PropTypes.string,
};

export default EventDateTimeDashboard;
