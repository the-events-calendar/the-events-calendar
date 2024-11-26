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
	{ label: _x('29/10/2024', 'example date in "d/m/Y" format', 'the-events-calendar'), value: 'd/m/Y' },
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
	const { defaultCurrencySymbol, timezone_string, date_format, start_of_week, timezones, currencies } = useSelect(
		(select) => {
			const store = select(SETTINGS_STORE_KEY);
			return {
				defaultCurrencySymbol: store.getSetting('defaultCurrencySymbol'),
				timezone_string: store.getSetting('timezone_string'),
				date_format: store.getSetting('date_format'),
				start_of_week: store.getSetting('start_of_week'),
				timezones: store.getSetting('timezones'),
				currencies: store.getSetting('currencies'),
			};
		},
		[]
	);
	const [ currency, setCurrency ] = useState( defaultCurrencySymbol );
	const [ timeZone, setTimeZone ] = useState( timezone_string );
	const [ dateFormat, setDateFormat ] = useState( date_format || dateFormatOptions[0].value );
	const [ weekStart, setWeekStart ] = useState( start_of_week || 0 );

	const timeZoneMessage = timezone_string && timezone_string.includes("UTC") ? __('Please select your time zone as UTC offsets are not supported.', 'the-events-calendar') : __("Please select your time zone.", 'the-events-calendar');

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		defaultCurrencySymbol: currency,
		timezone_string: timeZone,
		date_format: dateFormat,
		start_of_week: weekStart,
		currentTab: 2, // Include the current tab index.
	};

	return (
		<>
			<GearIcon />
			<h1 className="tec-events-onboarding__tab-header">{__('Event Settings', 'the-events-calendar')}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__('Let\’s get your events with the correct basic settings.', 'the-events-calendar')}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<SelectControl
					__nextHasNoMarginBottom
					label={__('Currency symbol', 'the-events-calendar')}
					defaultValue={ currency }
					onChange={ ( value ) => {
						setCurrency( value ) }
					}
				>
					{Object.entries(currencies).map(([key, data]) => (
						<option key={key} value={data['symbol']}>{data['symbol']} ({data['name']})</option>
					))}
				</SelectControl>
				{(!timezone_string || timezone_string.includes('UTC')) && (
					<>
						<SelectControl
							__nextHasNoMarginBottom
							label={__('Time Zone', 'the-events-calendar')}
							describedBy="time-zone-description"
							defaultValue={ timeZone }
							onChange={ setTimeZone }
						>
							<option value="">{__("Please select a non-UTC timezone.", 'the-events-calendar' )}</option>
							{Object.entries(timezones).map(([key, cities]) => (
								<optgroup key={key} className="continent" label={key}>
									{Object.entries(cities as {[key: string]: string}).map(([key, city]) => (
										<option key={key}  value={key}>{city}</option>
									))}
								</optgroup>
							))}
						</SelectControl>
						<span id="time-zone-description">{timeZoneMessage}</span>
					</>
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