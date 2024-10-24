import { __ } from '@wordpress/i18n';
import NextButton from '../buttons/next';
import SkipButton from '../buttons/skip';
import * as GearIcon from '../icons/gear';

const SettingsContent = ({closeModal, moveToNextTab}) => {
	return (
		<>
			<GearIcon.default />
			<h1>{__("Event Settings", "the-events-calendar")}</h1>
			<p>{__("Let’s get your events with the correct basic settings.", "the-events-calendar")}</p>
			<div>Form goes here</div>
			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton moveToNextTab={moveToNextTab}/></p>
		</>
	);
};

export default SettingsContent;
