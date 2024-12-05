import React from "react";
import { __, _x } from "@wordpress/i18n";
import { BaseControl, Button } from "@wordpress/components";
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import NextButton from "../../buttons/next";
import SkipButton from "../../buttons/skip";
import OrganizerIcon from "./img/organizer";

interface Organizer {
	organizerId: number;
	name: string;
	phone: string;
	website: string;
	email: string;
}

const OrganizerContent = ({moveToNextTab, skipToNextTab}) => {
	const organizer: Organizer = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('organizer') || { id: 0, name: '', phone: '', website: '', email: '' }, []);
	const visitedFields = useSelect(select => select(SETTINGS_STORE_KEY).getVisitedFields() || {} );
	const setVisitedField = useDispatch(SETTINGS_STORE_KEY).setVisitedField;
	const [organizerId, setId] = useState(organizer.organizerId || false);
	const [name, setName] = useState(organizer.name || '');
	const [phone, setPhone] = useState(organizer.phone || '');
	const [website, setWebsite] = useState(organizer.website || '');
	const [email, setEmail] = useState(organizer.email || '');
	const [showPhone, setShowPhone] = useState(!!organizer.phone);
	const [showWebsite, setShowWebsite] = useState(!!organizer.website);
	const [showEmail, setShowEmail] = useState(!!organizer.email);
	const [canContinue, setCanContinue] = useState(false);

	// Check if any fields are pre-filled.
	const disabled = !!organizer.organizerId;

	useEffect(() => {
		// Define the event listener function.
		const handleBlur = (event) => {
			setVisitedField(event.target.id);
		};

		const fields = document.getElementById('organizerPanel')?.querySelectorAll('input, select, textarea');
		fields?.forEach((field) => {
			field.addEventListener('blur', handleBlur);
		});

		return () => {
			fields?.forEach((field) => {
				field.removeEventListener('blur', handleBlur);
			});
		};
	}, []);

	const toggleClasses = (field, fieldEle, parentEle, isValid) => {
		if ( !field ) {
			parentEle.classList.add('invalid', 'empty');
			fieldEle.classList.add('invalid');
		} else if ( !isValid ) {
			parentEle.classList.add('invalid');
			fieldEle.classList.add('invalid');
		} else {
			parentEle.classList.remove('invalid', 'empty');
			fieldEle.classList.remove('invalid');
		}
	}

	// Compute whether the "Continue" button should be enabled
	useEffect(() => {
		const fieldsToCheck = {
			'organizer-name': isValidName(),
			'organizer-phone': isValidPhone(),
			'organizer-website': isValidWebsite(),
			'organizer-email': isValidEmail(),
			'visit-at-least-one': hasVisitedHere(),
		};
		setCanContinue(Object.values(fieldsToCheck).every((field) => !!field));
	}, [name, phone, website, email, showPhone, showWebsite, showEmail, visitedFields]);

	const hasVisitedHere = () => {
		const fields = ['organizer-name', 'organizer-phone', 'organizer-website', 'organizer-email'];
		return fields.some(field => visitedFields.includes(field));
	}

	const isValidName = () => {
		const inputId = 'organizer-name';
		const isVisited = visitedFields.includes(inputId);
		const isValid = !isVisited || !!name;
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if ( isVisited ) {
			toggleClasses(name, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidEmail = () => {
		const inputId = 'organizer-email';
		const isVisited = visitedFields.includes(inputId);
		const emailPattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		const isValid = !isVisited || emailPattern.test(email);
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if ( isVisited ) {
			toggleClasses(email, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidPhone = () => {
		const inputId = 'organizer-phone';
		const isVisited = visitedFields.includes(inputId);
		const phonePattern = /^\+?\d?[\s.-]?(?:\(\d{3}\)|\d{3})[\s.-]?\d{3}[\s.-]?\d{4}$/;
		const isValid = !isVisited || phonePattern.test(phone);
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if ( isVisited ) {
			toggleClasses(phone, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidWebsite = () => {
		const inputId = 'organizer-website';
		const isVisited = visitedFields.includes(inputId);
		const websitePattern = /^(http|https):\/\/[^ "]+?$/;
		const isValid = !isVisited || websitePattern.test(website);
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if ( isVisited ) {
			toggleClasses(website, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const showField = (event, fieldSetter) => {
		fieldSetter(true);
	}



	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		organizer: {
			organizerId,
			name,
			phone,
			website,
			email,
		},
		currentTab: 3, // Include the current tab index.
	};

	const subHeaderText = organizerId > 0 ?
		__('Looks like you have already created your first organizer. Well done!', 'the-events-calendar') :
		__('Add an event organizer for your events. You can display this information for your event attendees on your website.', 'the-events-calendar');

	return (
		<>
			<OrganizerIcon />
			<h1 className="tec-events-onboarding__tab-header">{__('Add your first event organizer', 'the-events-calendar')}</h1>
			<p className="tec-events-onboarding__tab-subheader">{subHeaderText}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<BaseControl
					__nextHasNoMarginBottom
					id="organizer-name"
					className="tec-events-onboarding__form-field"
					label={__('Organizer name', 'the-events-calendar')}
				>
					<input
						type="text"
						id="organizer-name"
						onChange={(e) => setName(e.target.value)}
						defaultValue={name}
						disabled={disabled}
						placeholder={__('Enter organizer name', 'the-events-calendar')}
					/>
					<span className="tec-events-onboarding__required-label">{__('Organizer name is required.', 'the-events-calendar')}</span>
				</BaseControl>
				{!organizerId && showPhone ? '' :
				<Button
					__next40pxDefaultSize
					onClick={(event) => showField(event, setShowPhone)}
					variant="tertiary"
					className="tec-events-onboarding__form-field-trigger"
				>
					{_x('Add a phone number +', 'Direction to add a phone number followed by a plus sign to indicate it shows a visually hidden field.', 'the-events-calendar')}
				</Button>}

				<BaseControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field"
					id="organizer-phone"
					label={__('Phone', 'the-events-calendar')}
				>
					<input
						id="organizer-phone"
						onChange={(e) => setPhone(e.target.value)}
						type="tel"
						defaultValue={phone}
						disabled={!showPhone || disabled}
						placeholder={__('Enter phone number', 'the-events-calendar')}
					/>
					<span className="tec-events-onboarding__required-label">{__('Organizer phone is required.', 'the-events-calendar')}</span>
					<span className="tec-events-onboarding__invalid-label">{__('Organizer phone is invalid.', 'the-events-calendar')}</span>
				</BaseControl>
				{!organizerId && showWebsite ? '' :
				<Button
					__next40pxDefaultSize
					onClick={(event) => showField(event, setShowWebsite)}
					variant="tertiary"
					className="tec-events-onboarding__form-field-trigger"
				>
					{_x('Add a website +', 'Direction to add a website followed by a plus sign to indicate it shows a visually hidden field.', 'the-events-calendar')}
				</Button>}
				<BaseControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field"
					id="organizer-website"
					label={__('Website', 'the-events-calendar')}
				>
					<input
						id="organizer-website"
						onChange={(e) => setWebsite(e.target.value)}
						type="url"
						defaultValue={website}
						disabled={!showWebsite || disabled}
						placeholder={__('Enter website', 'the-events-calendar')}
					/>
					<span className="tec-events-onboarding__required-label">{__('Organizer website is required.', 'the-events-calendar')}</span>
					<span className="tec-events-onboarding__invalid-label">{__('Organizer website is invalid.', 'the-events-calendar')}</span>
				</BaseControl>
				{!organizerId && showEmail ? '' :
				<Button
					__next40pxDefaultSize
					onClick={(event) => showField(event, setShowEmail)}
					variant="tertiary"
					className="tec-events-onboarding__form-field-trigger"
				>
					{_x('Add an email +', 'Direction to add an email followed by a plus sign to indicate it shows a visually hidden field.', 'the-events-calendar')}
				</Button>}
				<BaseControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field"
					id="organizer-email"
					label={__("Email", 'the-events-calendar')}
				>
					<input
						id="organizer-email"
						onChange={(e) => setEmail(e.target.value)}
						type="email"
						defaultValue={email}
						disabled={!showEmail || disabled}
						placeholder={__('Enter email', 'the-events-calendar')}
					/>
					<span className="tec-events-onboarding__required-label">{__('Organizer email is required.', 'the-events-calendar')}</span>
					<span className="tec-events-onboarding__invalid-label">{__('Organizer email is invalid.', 'the-events-calendar')}</span>
				</BaseControl>
			</div>

			 <p className="tec-events-onboarding__element--center"><NextButton disabled={!canContinue} moveToNextTab={moveToNextTab}  tabSettings={tabSettings}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab} currentTab={3} /></p>
		</>
	);
};

export default OrganizerContent;
