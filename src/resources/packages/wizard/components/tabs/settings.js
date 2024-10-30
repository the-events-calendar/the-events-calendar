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
				Form goes here?
			</div>

			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default SettingsContent;
