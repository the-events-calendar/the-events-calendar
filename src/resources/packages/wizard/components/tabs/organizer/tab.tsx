import React from "react";
import { __ } from "@wordpress/i18n";
import { TextControl, Button } from "@wordpress/components";
import NextButton from "../../buttons/next";
import SkipButton from "../../buttons/skip";
import OrganizerIcon from "./img/organizer";
import { _x } from "@wordpress/i18n";

const OrganizerContent = ({closeModal, moveToNextTab, skipToNextTab, bootData}) => {
	const {organizer} = bootData;
	const disabled = !!organizer;

	// Mocking data for now.
	const organizerObj = organizer ? {
		name: "The Events Calendar Joe",
		phone: "555-555-5555",
		website: "https://theeventscalendar.com",
		email: "organizer@theeventscalendar.com",
	} : null;

	/**
	 * Function to show hidden fields.
	 *
	 * @param {string} field The ID of the field to show.
	 */
	const showField = (event) => {
		const ele = event.target;
		ele.nextSibling.classList.remove("tec-events-onboarding__form-field--hidden");
		ele.style.display = "none";
	}

	return (
		<>
			<OrganizerIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Add your first event organizer.", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__("Add an event organizer for your events. You can display this information for your event attendees on your website.", "the-events-calendar")}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__("Organizer Name", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value={organizerObj && organizerObj.name ? organizerObj.name : ""}
					disabled={disabled}
				/>
				{organizerObj && organizerObj.phone ? "" :
				<Button
					__next40pxDefaultSize
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a phone number +", "Direction to add a phone number followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={organizerObj && organizerObj.phone ? "" : "tec-events-onboarding__form-field--hidden" }
					id="organizer-phone"
					label={__("Phone", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="tel"
					value={organizerObj && organizerObj.phone ? organizerObj.phone : ""}
					disabled={disabled}
				/>
				{organizerObj && organizerObj.website ? "" :
				<Button
					__next40pxDefaultSize
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a website +", "Direction to add a website followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={organizerObj && organizerObj.website ? "" : "tec-events-onboarding__form-field--hidden" }
					id="organizer-website"
					label={__("Website", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="url"
					value={organizerObj && organizerObj.website ? organizerObj.website : ""}
					disabled={disabled}
				/>
				{organizerObj && organizerObj.email ? "" :
				<Button
					__next40pxDefaultSize
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add an email +", "Direction to add an email followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={organizerObj && organizerObj.email ? "" : "tec-events-onboarding__form-field--hidden" }
					id="organizer-email"
					label={__("Email", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="email"
					value={organizerObj && organizerObj.email ? organizerObj.email : ""}
					disabled={disabled}
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab} disabled={false}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default OrganizerContent;
