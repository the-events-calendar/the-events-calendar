import React from "react";
import { __, _x } from "@wordpress/i18n";
import { TextControl, Button } from "@wordpress/components";
import { useState, useEffect } from '@wordpress/element';
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
	const [email, setEmail] = useState(organizer.email || "");    const [showPhone, setShowPhone] = useState(false);
	const [showWebsite, setShowWebsite] = useState(false);
	const [showEmail, setShowEmail] = useState(false);
	const [canContinue, setCanContinue] = useState(false);

	// Check if any fields are pre-filled.
	const disabled = !!organizer.name || !!organizer.phone || !!organizer.website || !!organizer.email;

	// Compute whether the "Continue" button should be enabled
    useEffect(() => {
        const fieldsToCheck = {
            'organizer-name': isValidName(),
            'organizer-phone': isValidPhone(),
            'organizer-website': isValidWebsite(),
            'organizer-email': isValidEmail(),
		};
		setCanContinue(Object.values(fieldsToCheck).every((field) => !!field));
    }, [name, phone, website, email, showPhone, showWebsite, showEmail]);

	const isValidName = () => {
		return !!name;
	}

	const isValidEmail = () => {
		const emailPattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		const isValid = !showEmail || emailPattern.test(email);
		const ele = document.getElementById("organizer-email");

		if (showEmail && !isValid) {
			ele?.classList.add("tec-events-onboarding__form-field--invalid");
		} else if (showEmail) {
			ele?.classList.remove("tec-events-onboarding__form-field--invalid");
		}

		return isValid;
	}

	const isValidPhone = () => {
		const phonePattern = /^\+?\d?[\s.-]?(?:\(\d{3}\)|\d{3})[\s.-]?\d{3}[\s.-]?\d{4}$/;
		const isValid = !showPhone || phonePattern.test(phone);
		const ele = document.getElementById("organizer-phone");

		if (showPhone && !isValid) {
			ele?.classList.add("tec-events-onboarding__form-field--invalid");
		} else if (showPhone) {
			ele?.classList.remove("tec-events-onboarding__form-field--invalid");
		}

		return isValid;
	}

	const isValidWebsite = () => {
		const websitePattern = /^(http|https):\/\/[^ "]+?$/;
		const isValid = !showWebsite || websitePattern.test(website);
		const ele = document.getElementById("organizer-website");

		if (showWebsite && !isValid) {
			ele?.classList.add("tec-events-onboarding__form-field--invalid");
		} else if (showWebsite) {
			ele?.classList.remove("tec-events-onboarding__form-field--invalid");
		}

		return isValid;
	}

	const showField = (event, fieldSetter) => {
		const ele = event.target;
		ele.nextSibling.classList.remove("tec-events-onboarding__form-field--hidden");
		ele.style.display = "none";

		fieldSetter(true);
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

	const subHeaderText = id > 0 ?
		__("Looks like you have already created your first organizer. Well done!", "the-events-calendar") :
		__("Add an event organizer for your events. You can display this information for your event attendees on your website.", "the-events-calendar");

	return (
		<>
			<OrganizerIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Add your first event organizer", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{subHeaderText}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					id="organizer-name"
					label={__("Organizer name", "the-events-calendar")}
					onChange={setName}
					defaultValue={name}
					disabled={disabled}
					placeholder={__("Enter organizer name", "the-events-calendar")}
				/>
				{phone ? "" :
				<Button
					__next40pxDefaultSize
					onClick={(event) => showField(event, setShowPhone)}
					variant="tertiary"
					className="tec-events-onboarding__form-field-trigger"
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
					disabled={!showPhone || disabled}
					placeholder={__("Enter phone number", "the-events-calendar")}
				/>
				{website ? "" :
				<Button
					__next40pxDefaultSize
					onClick={(event) => showField(event, setShowWebsite)}
					variant="tertiary"
					className="tec-events-onboarding__form-field-trigger"
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
					disabled={!showWebsite || disabled}
					placeholder={__("Enter website", "the-events-calendar")}
				/>
				{email ? "" :
				<Button
					__next40pxDefaultSize
					onClick={(event) => showField(event, setShowEmail)}
					variant="tertiary"
					className="tec-events-onboarding__form-field-trigger"
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
					disabled={!showEmail || disabled}
					placeholder={__("Enter email", "the-events-calendar")}
				/>
			</div>

			 <p className="tec-events-onboarding__element--center"><NextButton disabled={!canContinue} moveToNextTab={moveToNextTab}  tabSettings={tabSettings}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab} /></p>
		</>
	);
};

export default OrganizerContent;
