import React from "react";
import { __, _x } from "@wordpress/i18n";
import { TextControl, Button } from "@wordpress/components";
import { useState } from '@wordpress/element';
import { useSelect } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import NextButton from "../../buttons/next";
import SkipButton from "../../buttons/skip";
import OrganizerIcon from "./img/organizer";

interface Organizer {
	id: number;
	name: string;
	phone: string;
	website: string;
	email: string;
}

const OrganizerContent = ({moveToNextTab, skipToNextTab}) => {
	const organizer: Organizer = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("organizer") || { id: 0, name: "", phone: "", website: "", email: "" }, []);
	const [id, setId] = useState(organizer.id || 0);
	const [name, setName] = useState(organizer.name || "");
	const [phone, setPhone] = useState(organizer.phone || "");
	const [website, setWebsite] = useState(organizer.website || "");
	const [email, setEmail] = useState(organizer.email || "");

	// Check if any fields are filled.
	const disabled = !!organizer.name || !!organizer.phone || !!organizer.website || !!organizer.email;

	/**
	 * Function to show hidden fields.
	 */
	const showField = (event) => {
		const ele = event.target;
		ele.nextSibling.classList.remove("tec-events-onboarding__form-field--hidden");
		ele.style.display = "none";
	}

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		organizer: {
			id,
			name,
			phone,
			website,
			email,
		},
		currentTab: 3, // Include the current tab index.
	};

	const subHeaderText = id ?
		__("Add an event organizer for your events. You can display this information for your event attendees on your website.", "the-events-calendar")
		: __("Looks like you have already created your first organizer. Well done!", "the-events-calendar");

	return (
		<>
			<OrganizerIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Add your first event organizer.", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{subHeaderText}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__("Organizer Name", "the-events-calendar")}
					onChange={setName}
					defaultValue={name}
					disabled={disabled}
				/>
				{phone ? "" :
				<Button
					__next40pxDefaultSize
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a phone number +", "Direction to add a phone number followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={phone ? "" : "tec-events-onboarding__form-field--hidden" }
					id="organizer-phone"
					label={__("Phone", "the-events-calendar")}
					onChange={setPhone}
					type="tel"
					defaultValue={phone}
					disabled={disabled}
				/>
				{website ? "" :
				<Button
					__next40pxDefaultSize
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a website +", "Direction to add a website followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={website ? "" : "tec-events-onboarding__form-field--hidden" }
					id="organizer-website"
					label={__("Website", "the-events-calendar")}
					onChange={setWebsite}
					type="url"
					defaultValue={website}
					disabled={disabled}
				/>
				{email ? "" :
				<Button
					__next40pxDefaultSize
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add an email +", "Direction to add an email followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={email ? "" : "tec-events-onboarding__form-field--hidden" }
					id="organizer-email"
					label={__("Email", "the-events-calendar")}
					onChange={setEmail}
					type="email"
					defaultValue={email}
					disabled={disabled}
				/>
			</div>

			 <p className="tec-events-onboarding__element--center"><NextButton disabled={false} moveToNextTab={moveToNextTab}  tabSettings={tabSettings}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab} /></p>
		</>
	);
};

export default OrganizerContent;
