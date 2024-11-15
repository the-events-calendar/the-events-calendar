import React from 'react';
import { SelectControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
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

const SettingsContent = ({moveToNextTab, skipToNextTab}) => {
	const { defaultCurrencySymbol, defaultTimezone, defaultDateFormat, defaultWeekStart, timezones } = useSelect(
		(select) => {
			const store = select(SETTINGS_STORE_KEY);
			return {
				defaultCurrencySymbol: store.getSetting('defaultCurrencySymbol'),
				defaultTimezone: store.getSetting('defaultTimezone'),
				defaultDateFormat: store.getSetting('defaultDateFormat'),
				defaultWeekStart: store.getSetting('defaultWeekStart'),
				timezones: store.getSetting('timezones'),
			};
		},
		[]
	);
	const [ currency, setCurrency ] = useState( defaultCurrencySymbol );
	const [ timeZone, setTimeZone ] = useState( defaultTimezone );
	const [ dateFormat, setDateFormat ] = useState( defaultDateFormat || dateFormatOptions[0].value );
	const [ weekStart, setWeekStart ] = useState( defaultWeekStart || 0 );

	const timeZoneMessage = defaultTimezone && defaultTimezone.includes("UTC") ? __('Please select your time zone as UTC offsets are not supported.', 'the-events-calendar') : __("Please select your time zone.", 'the-events-calendar');

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		defaultCurrencySymbol: currency,
		defaultTimezone: timeZone,
		defaultDateFormat: dateFormat,
		defaultWeekStart: weekStart,
		currentTab: 2, // Include the current tab index.
	};

	return (
		<>
			<GearIcon />
			<h1 className="tec-events-onboarding__tab-header">{__('Event Settings', 'the-events-calendar')}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__('Let\’s get your events with the correct basic settings.', 'the-events-calendar')}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__('Currency', 'the-events-calendar')}
					defaultValue={ currency }
					onChange={ ( value ) => {
						setCurrency( value ) }
					}
				/>
				{(!defaultTimezone || defaultTimezone.includes('UTC')) && (
					<SelectControl
						__nextHasNoMarginBottom
						label={__('Time Zone', 'the-events-calendar')}
						description={timeZoneMessage}
						defaultValue={ timeZone }
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
					label={__('Date Format', 'the-events-calendar')}
					defaultValue={ dateFormat }
					onChange={ setDateFormat }
					options={dateFormatOptions}
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__('Your Week starts on', 'the-events-calendar')}
					defaultValue={ weekStart }
					onChange={ setWeekStart }
					options={startDayOptions}
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton disabled={false} moveToNextTab={moveToNextTab} tabSettings={tabSettings}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab} /></p>
		</>
	);
};

export default SettingsContent;
