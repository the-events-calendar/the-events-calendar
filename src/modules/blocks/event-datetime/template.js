/**
 * External dependencies
 */
import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { InspectorControls, PlainText } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { TimePicker } from '@moderntribe/common/elements';
import {
	Dashboard,
	Month,
	Upsell,
	TimeZone,
} from '@moderntribe/events/elements';
import {
	date,
	moment as momentUtil,
	time,
} from '@moderntribe/common/utils';
import { editor, settings, editorConstants } from '@moderntribe/common/utils/globals';
import './style.pcss';
import HumanReadableInput from './human-readable-input/container';
import { DateTimePluginBlockHook, DescriptionPluginBlockHook } from './hooks';

/**
 * Module Code
 */

const { FORMATS, TODAY, timezonesAsSelectData } = date;
const {
	roundTime,
	toMoment,
	toDate,
	toDateNoYear,
	toTime,
	isSameYear,
} = momentUtil;
FORMATS.date = settings() && settings().dateWithYearFormat
	? settings().dateWithYearFormat
	: __( 'F j', 'events-gutenberg' );

class EventDateTime extends Component {
	static propTypes = {
		allDay: PropTypes.bool,
		multiDay: PropTypes.bool,
		isDashboardOpen: PropTypes.bool,
		cost: PropTypes.string,
		start: PropTypes.string,
		end: PropTypes.string,
		separatorDate: PropTypes.string,
		separatorTime: PropTypes.string,
		timeZone: PropTypes.string,
		showTimeZone: PropTypes.bool,
		timeZoneLabel: PropTypes.string,
		showDateInput: PropTypes.bool,
		setTimeZoneLabel: PropTypes.func,
		currencyPosition: PropTypes.oneOf( [ 'prefix', 'suffix', '' ] ),
		currencySymbol: PropTypes.string,
		naturalLanguageLabel: PropTypes.string,
		setInitialState: PropTypes.func,
		setCost: PropTypes.func,
		setTimeZone: PropTypes.func,
		setSeparatorTime: PropTypes.func,
		setSeparatorDate: PropTypes.func,
		setVisibleMonth: PropTypes.func,
		setNaturalLanguageLabel: PropTypes.func,
		onKeyDown: PropTypes.func,
		onClick: PropTypes.func,
		onSelectDay: PropTypes.func,
		onStartTimePickerChange: PropTypes.func,
		onStartTimePickerClick: PropTypes.func,
		onEndTimePickerChange: PropTypes.func,
		onEndTimePickerClick: PropTypes.func,
		onMultiDayToggleChange: PropTypes.func,
		onTimeZoneVisibilityChange: PropTypes.func,
		onDateTimeLabelClick: PropTypes.func,
		visibleMonth: PropTypes.instanceOf( Date ),
		isEditable: PropTypes.bool.isRequired,
	};

	componentDidMount() {
		const { onKeyDown, onClick } = this.props;
		document.addEventListener( 'keydown', onKeyDown );
		document.addEventListener( 'click', onClick );
	}

	componentWillUnmount() {
		const { onKeyDown, onClick } = this.props;
		document.removeEventListener( 'keydown', onKeyDown );
		document.removeEventListener( 'click', onClick );
	}

	renderPrice = () => {
		const { cost, currencyPosition, currencySymbol, setCost } = this.props;

		// Bail when not classic
		if ( ! editor() || ! editor().is_classic ) {
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
					placeholder={ __( 'Enter price', 'events-gutenberg' ) }
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
					<span className={ classNames( 'tribe-editor__separator', className ) }>{ __( 'ALL DAY', 'events-gutenberg' ) }</span>
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

	renderDashboard = () => {
		const { isDashboardOpen, multiDay, allDay } = this.props;
		const hideUpsell = editorConstants().hide_upsell === 'true';

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
									( multiDay || ! allDay ) &&
									this.renderSeparator( 'time-range', 'tribe-editor__time-picker__separator' )
								}
								{ this.renderEndTimePicker() }
							</div>
							<div className="tribe-editor__subtitle__footer-multiday">
								{ this.renderMultiDayToggle() }
							</div>
						</div>
						<DateTimePluginBlockHook />
						{ ! hideUpsell && <Upsell /> }
					</footer>
				</Fragment>
			</Dashboard>
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

	/**
	 * Main label used to display the event
	 *
	 * @returns {ReactDOM} A React Dom Element null if none.
	 */
	renderBlock = () => {
		const {
			multiDay,
			allDay,
			showDateInput,
			onDateTimeLabelClick,
			isEditable,
		} = this.props;

		return (
			<section
				key="event-datetime"
				className="tribe-editor__subtitle tribe-editor__date-time"
			>
				{
					showDateInput && isEditable
						? (
							<HumanReadableInput after={ this.renderExtras() } />
						)
						: (
							<Fragment>
								<h2 className="tribe-editor__subtitle__headline">
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
								</h2>
								<DescriptionPluginBlockHook />
							</Fragment>
						)
				}
				{ isEditable && this.renderDashboard() }
			</section>
		);
	}

	/**
	 * Controls being rendered on the sidebar.
	 *
	 * @returns {ReactDOM} A React Dom Element null if none.
	 */
	renderControls = () => {
		const {
			separatorTime,
			separatorDate,
			timeZone,
			setTimeZone,
			setSeparatorTime,
			setSeparatorDate,
			showTimeZone,
			onTimeZoneVisibilityChange,
		} = this.props;

		// @todo: modify so this code does not fire unless the block is selected
		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Date Time Settings', 'events-gutenberg' ) }>
					<TextControl
						label={ __( 'Date Time Separator', 'events-gutenberg' ) }
						value={ separatorDate }
						onChange={ setSeparatorDate }
						className="tribe-editor__date-time__date-time-separator-setting"
						maxLength="2"
					/>
					<TextControl
						label={ __( 'Time Range Separator', 'events-gutenberg' ) }
						value={ separatorTime }
						onChange={ setSeparatorTime }
						className="tribe-editor__date-time__time-range-separator-setting"
						maxLength="2"
					/>
					<SelectControl
						label={ __( 'Time Zone', 'events-gutenberg' ) }
						value={ timeZone }
						onChange={ setTimeZone }
						options={ timezonesAsSelectData() }
						className="tribe-editor__date-time__time-zone-setting"
					/>
					<ToggleControl
						label={ __( 'Show Time Zone', 'events-gutenberg' ) }
						checked={ showTimeZone }
						onChange={ onTimeZoneVisibilityChange }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}

	render() {
		return [
			this.renderBlock(),
			this.props.isEditable && this.renderControls(),
		];
	}
}

export default EventDateTime;
