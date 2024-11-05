import React from "react";
import { SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import GearIcon from './img/gear';

const dateFormatOptions = [
	{ label: _x('October 29, 2024', 'example date in "F j, Y" format', 'the-events-calendar'), value: 'F j, Y' },
	{ label: _x('29 October, 2024', 'example date in "j F, Y" format', 'the-events-calendar'), value: 'j F, Y' },
	{ label: _x('10/29/2024', 'example date in "m/d/Y" format', 'the-events-calendar'), value: 'm/d/Y' },
	{ label: _x('2024-10-29', 'example date in "Y-m-d" format', 'the-events-calendar'), value: 'Y-m-d' },
];

const startDayOptions = [
	{ label: __('Sunday', 'the-events-calendar'), value: '0' },
	{ label: __('Monday', 'the-events-calendar'), value: '1' },
	{ label: __('Tuesday', 'the-events-calendar'), value: '2' },
	{ label: __('Wednesday', 'the-events-calendar'), value: '3' },
	{ label: __('Thursday', 'the-events-calendar'), value: '4' },
	{ label: __('Friday', 'the-events-calendar'), value: '5' },
	{ label: __('Saturday', 'the-events-calendar'), value: '6' },
];

const currencyOptions = [
	{ label: 'USD', value: 'USD' },
	{ label: 'CAD', value: 'CAD' },
	{ label: 'GBP', value: 'GBP' },
];

const SettingsContent = ({closeModal, moveToNextTab, skipToNextTab, bootData}) => {
	const {defaultCurrency, defaultTimezone, defaultDateFormat, defaultWeekStart, timezones}: {defaultCurrency: string, defaultTimezone: string, defaultDateFormat: string, defaultWeekStart: string, timezones: {[key: string]: {[key: string]: string}}} = bootData;
	const [ currency, setCurrency ] = useState( defaultCurrency );
	const [ timeZone, setTimeZone ] = useState( defaultTimezone );
	const [ dateFormat, setDateFormat ] = useState( defaultDateFormat );
	const [ weekStart, setWeekStart ] = useState( defaultWeekStart );

	return (
		<>
			<GearIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Event Settings", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__("Let’s get your events with the correct basic settings.", "the-events-calendar")}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<SelectControl
					__nextHasNoMarginBottom
					label={__("Currency", "the-events-calendar")}
					value={ currency }
					onChange={ setCurrency }
					options={currencyOptions}
				/>
				{!defaultTimezone && (
					<SelectControl
						__nextHasNoMarginBottom
						label={__("Time Zone", "the-events-calendar")}
						description={__("Please select your time zone as UTC offsets are not supported.", "the-events-calendar")}
						value={ timeZone }
						onChange={ setTimeZone }
					>
						{Object.entries(timezones).map(([key, cities]) => (
							<optgroup key={key} className="continent" label={key}>
								{Object.entries(cities as {[key: string]: string}).map(([key, city]) => (
									<option key={key}  value={key}>{city}</option>
								))}
							</optgroup>
						))}
					</SelectControl>
				)}

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Date Format", "the-events-calendar")}
					value={ dateFormat }
					onChange={ setDateFormat }
					options={dateFormatOptions}
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Your Week starts on", "the-events-calendar")}
					value={ weekStart }
					onChange={ setWeekStart }
					options={startDayOptions}
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab} disabled={false}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default SettingsContent;
