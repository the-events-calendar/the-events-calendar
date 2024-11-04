import React from "react";
import { __ } from '@wordpress/i18n';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import TicketsIcon from './img/tickets';
import TicketInstallCheckbox from './inputs/ticket-install';

const TicketsContent = ({closeModal, moveToNextTab, bootData}) => {
	const {eventTickets} = bootData;

	return (
		<>
			<div className='.tec-events-onboarding__content-checkbox-grid'>
				<TicketsIcon />
				<div className="tec-events-onboarding__content-grid">
					<h1 className="tec-events-onboarding__tab-header">{__("Event Tickets", "the-events-calendar")}</h1>
					<p className="tec-events-onboarding__tab-subheader">{__("Will you be selling tickets or providing attendees the ability to RSVP to your events?", "the-events-calendar")}</p>
					<TicketInstallCheckbox hasTickets={eventTickets}/>
					 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab}/></p>
					<p><SkipButton skipToNextTab={closeModal}/></p>
				</div>
			</div>
		</>
	);
};

export default TicketsContent;
