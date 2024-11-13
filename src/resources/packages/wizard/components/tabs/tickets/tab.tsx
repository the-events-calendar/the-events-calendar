import React from "react";
import {__} from '@wordpress/i18n';
import {useState, useEffect} from '@wordpress/element';
import {useSelect} from "@wordpress/data";
import {SETTINGS_STORE_KEY} from "../../../data";
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import TicketsIcon from './img/tickets';
import TicketInstallCheckbox from './inputs/ticket-install';

const TicketsContent = ({moveToNextTab, skipToNextTab}) => {
	const eventTickets = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("eventTickets") || false, []);
	const [originalValue] = useState(eventTickets);
	const [ticketValue, setTicketValue] = useState(eventTickets); // Store the updated ticket value.

	useEffect(() => {
		// Update the local state if the optin value changes
		setTicketValue(eventTickets);
	}, [eventTickets]);

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		eventTickets: ticketValue,
		currentTab: 5, // Include the current tab index.
	}

	return (
		<>
			<div className='.tec-events-onboarding__content-checkbox-grid'>
				<TicketsIcon />
				<div className="tec-events-onboarding__content-grid">
					<h1 className="tec-events-onboarding__tab-header">{__("Event Tickets", "the-events-calendar")}</h1>
					<p className="tec-events-onboarding__tab-subheader">{__("Will you be selling tickets or providing attendees the ability to RSVP to your events?", "the-events-calendar")}</p>
					{!originalValue &&(
						<TicketInstallCheckbox />
					)}
					 <p className="tec-events-onboarding__element--center"><NextButton tabSettings={false} moveToNextTab={moveToNextTab} disabled={false}/></p>
					<p><SkipButton skipToNextTab={skipToNextTab} /></p>
				</div>
			</div>
		</>
	);
};

export default TicketsContent;
