import React from "react";
import { SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import GearIcon from './img/gear';

const SettingsContent = ({closeModal, moveToNextTab, skipToNextTab, bootData}) => {
	const {defaultCurrency, defaultTimezone, defaultDateFormat, defaultWeekStart} = bootData;
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
					options={ [
						{ label: 'USD', value: 'USD' },
						{ label: 'CAD', value: 'CAD' },
						{ label: 'GBP', value: 'GBP' },
					] }
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Time Zone", "the-events-calendar")}
					description={__("Please select your time zone as UTC offsets are not supported.", "the-events-calendar")}
					value={ timeZone }
					onChange={ setTimeZone }
					options={ [
						{ label: 'GMT', value: 'UTC' },
						{ label: 'America/New York', value: 'EDT' },
						{ label: 'America/LosAngeles', value: 'PDT' },
					] }
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Date Format", "the-events-calendar")}
					value={ dateFormat }
					onChange={ setDateFormat }
					options={ [
						{ label: 'October 29, 2024', value: 'American' },
						{ label: '29 October, 2024', value: 'European' },
						{ label: '10/29/2024', value: 'shortAmerican' },
						{ label: '2024-10-29', value: 'shortEuropean' },
					] }
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Your Week starts on", "the-events-calendar")}
					value={ weekStart }
					onChange={ setWeekStart }
					options={ [
						{ label: 'Sunday', value: '0' },
						{ label: 'Monday', value: '1' },
						{ label: 'Tuesday', value: '2' },
						{ label: 'Wednesday', value: '3' },
						{ label: 'Thursday', value: '4' },
						{ label: 'Friday', value: '5' },
						{ label: 'Saturday', value: '6' },
					] }
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default SettingsContent;
