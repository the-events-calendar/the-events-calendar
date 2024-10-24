import { __ } from '@wordpress/i18n';
import NextButton from '../buttons/next';
import SkipButton from '../buttons/skip';
import * as TicketsIcon from '../icons/tickets';

const TicketsContent = ({closeModal, moveToNextTab}) => {
	return (
		<>
			<TicketsIcon.default />
			<h1>{__("Event Tickets", "the-events-calendar")}</h1>
			<p>{__("Will you be selling tickets or providing attendees the ability to RSVP to your events?", "the-events-calendar")}</p>
			<div>Form goes here</div>
			<p><NextButton moveToNextTab={moveToNextTab}/></p>
			<p><SkipButton moveToNextTab={closeModal}/></p>
		</>
	);
};

export default TicketsContent;
