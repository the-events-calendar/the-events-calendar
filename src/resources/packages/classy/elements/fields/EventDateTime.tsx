import React, {Fragment} from 'react';
import {useState} from '@wordpress/element';
import {useSelect} from '@wordpress/data';
import {EventDateTimeDetails} from '../../types/EventDateTimeDetails';
import {__experimentalInputControl as InputControl, DatePicker, TimePicker, ToggleControl} from '@wordpress/components';
import {DatePickerEvent} from '@wordpress/components/build-types/date-time/types';
import {_x} from '@wordpress/i18n';
import {METADATA_EVENT_END_DATE, METADATA_EVENT_START_DATE} from "../../constants";
import {format, getDate} from "@wordpress/date";
import {usePostEdits} from "../../hooks";
import {UsePostEditsReturn} from "../../types/UsePostEditsReturn";

export type EventDateTimeProps = {
	title: string;
};

const phpDateMysqlFormat =  'Y-m-d H:i:s' ;

function getDatesBetween(start: Date, end: Date): Date[] {
	const dateArray = [];
	let currentDate = new Date(start);
	while (currentDate <= end) {
		dateArray.push(new Date(currentDate));
		currentDate.setDate(currentDate.getDate() + 1);
	}
	return dateArray;
}

export function EventDateTime(props: EventDateTimeProps): React.FC {
	const {
		eventStart,
		eventEnd,
		isMultiday,
		isAllDay,
		eventTimezone,
		startOfWeek,
		dateFormat
	} =
		useSelect((select) => {
			const {
				getEventDateTimeDetails,
			}: { getEventDateTimeDetails: () => EventDateTimeDetails } =
				select('tec/classy');
			return getEventDateTimeDetails();
		}, []);
	const {editPost} = usePostEdits() as UsePostEditsReturn;

	const [isSelectingDate, setIsSelectingDate] = useState<'start' | 'end' | false>(false);
	const [dates, setDates] = useState({start: eventStart, end: eventEnd});
	const {start: startDate, end: endDate} = dates;
	const startDateFormattedValue = format(dateFormat, startDate);
	const endDateFormattedValue = format(dateFormat, endDate);
	const events = getDatesBetween(startDate, endDate).map((date: Date): DatePickerEvent => {
		return {date}
	});

	const eventStartTimeInput = {
		hours: startDate.getHours(),
		minutes: eventStart.getMinutes()
	};
	const eventEndTimeInput = {
		hours: endDate.getHours(),
		minutes: endDate.getMinutes()
	};

	/**
	 * Updates the start and end dates when a date change happens.
	 *
	 * @param {'start' | 'end'} updated The updated date property; either start or end date.
	 * @param {string} newDate The new date for the date property in the ISO 8601 format.
	 */
	const onDateChange = (updated: 'start' | 'end', newDate: string): void => {
		const previousDurationInMs = Math.abs(endDate.getTime() - startDate.getTime());
		let newStartDate:Date;
		let newEndDate:Date;

		if(updated === 'start'){
			// The start date has been updated by the user.
			newStartDate = getDate(newDate);
			newEndDate = endDate;

			if(newStartDate.getTime() >= endDate.getTime() ){
				// The start date is after the current end date: push the end date forward.
				newEndDate = new Date(newStartDate.getTime() + previousDurationInMs);
			}
		} else {
			// The end date has been updated by the user.
			newStartDate = startDate;
			newEndDate = getDate(newDate);

			if(newEndDate.getTime() < startDate.getTime()){
				// The end date is before the current start date: push the start date back.
				newStartDate = new Date(newEndDate.getTime() - previousDurationInMs);
			}
		}

		editPost({
			meta: {
				[METADATA_EVENT_START_DATE]: format(phpDateMysqlFormat, newStartDate),
				[METADATA_EVENT_END_DATE]: format(phpDateMysqlFormat, newEndDate)
			}
		});

		setDates({start: newStartDate, end: newEndDate});
		setIsSelectingDate(false);
	};

	const onDateInputClick = (selecting: 'start' | 'end') => {
		if(selecting === isSelectingDate){
			// Do nothing, we're already selecting this date.
			return;
		}

		return setIsSelectingDate(selecting);
	};

	const onStartTimeChange = (newDate:string) => onDateChange('start',newDate );
	const onEndTimeChange = (newDate:string) => onDateChange('end', newDate);

	const datePicker = isSelectingDate ?
		(<DatePicker
			startOfWeek={startOfWeek}
			currentDate={isSelectingDate === 'start' ? startDate : endDate}
			onChange={(newDate: string): void => onDateChange(isSelectingDate, newDate)}
			events={events}
		></DatePicker>) : null;

	const startSelector = (
		<Fragment>
			<InputControl
				value={startDateFormattedValue}
				onClick={()=>onDateInputClick('start')}
			></InputControl>
			{!isAllDay && <TimePicker.TimeInput
				value={eventStartTimeInput}
				onChange={onStartTimeChange}
			></TimePicker.TimeInput>}
		</Fragment>
	);


	const endSelector = (
		<Fragment>
			{isMultiday && <InputControl
				value={endDateFormattedValue}
				onClick={()=>onDateInputClick('end')}
			/>}
			{isAllDay ?
				<p>{_x('All Day', 'All day label in the date/time Classy selection field', 'the-events-calendar')}</p>
				: <TimePicker.TimeInput
					value={eventEndTimeInput}
					onChange={onEndTimeChange}
				></TimePicker.TimeInput>}
		</Fragment>
	);

	const onMultiDayToggleChange = () => {
		// Move the end day to the next day.
		const newEndDate = new Date(endDate.getTime() + 86400 * 1000);
		onDateChange('end', format(phpDateMysqlFormat, newEndDate));
	}

	const onAllDayToggleChange = () => console.log("all day toggle change");

	return (
		<div className="classy-field classy-field--event-datetime">
			<div className="classy-field__title">
				<h3>{props.title}</h3>
			</div>

			<div className="classy-field__inputs">
				{datePicker}
				{startSelector}
				{endSelector}
				<ToggleControl
					__nextHasNoMarginBottom
					label={_x('Multi-day event', 'Multi-day toggle label', 'the-events-calendar')}
					checked={isMultiday}
					onChange={onMultiDayToggleChange}
				></ToggleControl>

				<ToggleControl
					__nextHasNoMarginBottom
					label={_x('All-day event', 'All-day toggle label', 'the-events-calendar')}
					checked={isAllDay}
					onChange={onAllDayToggleChange}
				/>
			</div>
		</div>
	);
}
