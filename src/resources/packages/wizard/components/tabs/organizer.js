import { __ } from '@wordpress/i18n';
import NextButton from '../buttons/next';
import SkipButton from '../buttons/skip';
import * as OrganizerIcon from '../icons/organizer';

const OrganizerContent = ({closeModal, moveToNextTab}) => {
	return (
		<>
			<OrganizerIcon.default />
			<h1>{__("Add your first event organizer.", "the-events-calendar")}</h1>
			<p>{__("Add an event organizer for your events. You can display this information for your event attendees on your website.", "the-events-calendar")}</p>
			<div>Form goes here</div>
			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton moveToNextTab={moveToNextTab}/></p>
		</>
	);
};

export default OrganizerContent;
