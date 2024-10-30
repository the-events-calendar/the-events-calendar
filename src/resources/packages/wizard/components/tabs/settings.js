import { SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import NextButton from '../buttons/next';
import SkipButton from '../buttons/skip';
import GearIcon from '../icons/gear';

const SettingsContent = ({closeModal, moveToNextTab, skipToNextTab}) => {
	const [ currency, setCurrency ] = useState( 'USD' );
	const [ timeZone, setTimeZone ] = useState( 'UTC' );
	const [ dateFormat, setDateFormat ] = useState( 'American' );
	const [ weekStart, setWeekStart ] = useState( '1' );

	return (
		<>
			<GearIcon />
			<h1>{__("Event Settings", "the-events-calendar")}</h1>
			<p>{__("Let’s get your events with the correct basic settings.", "the-events-calendar")}</p>
			<div class="tec-events-onboarding__form-wrapper">
			<SelectControl
					__nextHasNoMarginBottom
					label={__("Currency", "the-events-calendar")}
					value={ currency }
					options={ [
						{ label: 'USD', value: 'USD' },
						{ label: 'CAD', value: 'CAD' },
						{ label: 'GBP', value: 'GBP' },
					] }
					onChange={ setCurrency }
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Time Zone", "the-events-calendar")}
					value={ timeZone }
					options={ [
						{ label: 'GMT', value: 'UTC' },
						{ label: 'America/New York', value: 'EDT' },
						{ label: 'America/LosAngeles', value: 'PDT' },
					] }
					onChange={ setTimeZone }
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Date Format", "the-events-calendar")}
					value={ dateFormat }
					options={ [
						{ label: 'October 29, 2024', value: 'American' },
						{ label: '29 October, 2024', value: 'European' },
						{ label: '10/29/2024', value: 'shortAmerican' },
						{ label: '2024-10-29', value: 'shortEuropean' },
					] }
					onChange={ setDateFormat }
				/>

				<SelectControl
					__nextHasNoMarginBottom
					label={__("Your Week starts on", "the-events-calendar")}
					value={ weekStart }
					options={ [
						{ label: 'Sunday', value: '0' },
						{ label: 'Monday', value: '1' },
						{ label: 'Tuesday', value: '2' },
						{ label: 'Wednesday', value: '3' },
						{ label: 'Thursday', value: '4' },
						{ label: 'Friday', value: '5' },
						{ label: 'Saturday', value: '6' },
					] }
					onChange={ setWeekStart }
				/>
			</div>

			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default SettingsContent;
