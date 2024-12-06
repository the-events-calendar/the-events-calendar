import React from "react";
import {__} from '@wordpress/i18n';
import {useSelect} from "@wordpress/data";
import { CheckboxControl } from '@wordpress/components';
import {useState, useEffect} from '@wordpress/element';
import {SETTINGS_STORE_KEY} from "../../../data";
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import TicketsIcon from './img/tickets';

const TicketsContent = ({moveToNextTab, skipToNextTab}) => {
	const currentTab = 5;
	const eventTicketsInstalled = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("event-tickets-installed") || false, []);
	const eventTicketsActive = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("event-tickets-active") || false, []);
	const [ticketValue, setTicketValue] = useState(eventTicketsInstalled); // Store the updated ticket value.

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		eventTickets: ticketValue,
		currentTab: currentTab, // Include the current tab index.
	}

	const message = (!eventTicketsInstalled) ? __("Yes, install and activate Event Tickets for free on my website.", "the-events-calendar") : __("Activate the Event Tickets Plugin for me.", "the-events-calendar");

	return (
		<>
			<div className='tec-events-onboarding__content-checkbox-grid'>
				<TicketsIcon />
				<div className="tec-events-onboarding__content-grid">
					<h1 className="tec-events-onboarding__tab-header">{__("Event Tickets", "the-events-calendar")}</h1>
					<p className="tec-events-onboarding__tab-subheader">{__("Will you be selling tickets or providing attendees the ability to RSVP to your events?", "the-events-calendar")}</p>
					{!eventTicketsActive &&(
						<div
							alignment="top"
							justify="center"
							spacing={0}
							className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--tickets"
						>
							<CheckboxControl
								__nextHasNoMarginBottom
								aria-describedby="tec-events-onboarding__checkbox-description"
								checked={true}
								onChange={setTicketValue}
								id="tec-events-onboarding__tickets-checkbox-input"
							/>
							<div className="tec-events-onboarding__checkbox-description">
								<label htmlFor="tec-events-onboarding__tickets-checkbox-input">
									{message}
								</label>
								<div
									id="tec-events-onboarding__checkbox-description"
								>
								</div>
							</div>
						</div>
					)}
					 <p className="tec-events-onboarding__element--center">
						<NextButton tabSettings={tabSettings} moveToNextTab={moveToNextTab} disabled={false}/>
					</p>
					<p><SkipButton skipToNextTab={skipToNextTab} currentTab={currentTab} /></p>
				</div>
			</div>
		</>
	);
};

export default TicketsContent;
