import React from "react";
import { __, _x } from '@wordpress/i18n';
import { TextControl, SelectControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import VenueIcon from './img/venue';

interface Venue {
	name: string;
	address: string;
	city: string;
	state: string;
	zip: string;
	country: string;
	phone: string;
	website: string;
}

const VenueContent = ({moveToNextTab, skipToNextTab}) => {
	const venue: Venue = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("venue")
		|| { name: "", address: "", city: "", state: "", zip: "", country: "", phone: "", website: "", }, []);

	// Check if any fields are filled.
	const disabled = !!venue.name || !!venue.address || !!venue.city || !!venue.state || !!venue.zip || !!venue.country || !!venue.phone || !!venue.website;

	const { updateSettings } = useDispatch(SETTINGS_STORE_KEY);
	const [name, setName] = useState(venue.name || "");
	const [address, setAddress] = useState(venue.address || "");
	const [city, setCity] = useState(venue.city || "");
	const [state, setState] = useState(venue.state || "");
	const [zip, setZip] = useState(venue.zip || "");
	const [country, setCountry] = useState(venue.country || "");
	const [phone, setPhone] = useState(venue.phone || "");
	const [website, setWebsite] = useState(venue.website || "");

	// Save the checked views to the store on "Continue" button click.
	const handleContinue = () => {
		const updates: Record<string, any> = {};

	// Define the local state for the properties
	const localState = { name, address, city, state, zip, country, phone, website };

	// Loop through each key in the venue object to compare with localState
	Object.keys(venue).forEach((key) => {
		if (localState[key] !== venue[key]) {
			updates[key] = localState[key];
		}
	});

	// Dispatch updates if any changes were detected
	if (Object.keys(updates).length > 0) {
		updateSettings({ organizer: { ...venue, ...updates } });
	}

		moveToNextTab();
	};

	/**
	 * Function to show hidden fields.
	 */
	const showField = (event) => {
		const ele = event.target;
		ele.nextSibling.classList.remove("tec-events-onboarding__form-field--hidden");
		ele.style.display = "none";
	}

	return (
		<>
			<VenueIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Add your first event venue.", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__("Show your attendees where they need to go to get to your events. You can display the location using Google Maps on your event pages.", "the-events-calendar")}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__("Venue Name", "the-events-calendar")}
					onChange={setName}
					defaultValue={name}
					disabled={disabled}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Address", "the-events-calendar")}
					onChange={setAddress}
					defaultValue={address}
					disabled={disabled}
					type="text"
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("City", "the-events-calendar")}
					onChange={setCity}
					defaultValue={city}
					disabled={disabled}
					type="text"
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("State or province", "the-events-calendar")}
					onChange={setState}
					defaultValue={state}
					disabled={disabled}
					type="text"
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Zip / Postal code", "the-events-calendar")}
					onChange={setZip}
					defaultValue={zip}
					disabled={disabled}
					type="text"
				/>
				<SelectControl
					__nextHasNoMarginBottom
					label={__("Country", "the-events-calendar")}
					onChange={setCountry}
					defaultValue={country}
					disabled={disabled}
					options={ [
						// These don't need translations - they need to come from somewhere else - WP core has a list I believe.
						{ label: 'Australia', value: 'AU' },
						{ label: 'Brazil', value: 'BR' },
						{ label: 'Canada', value: 'CA' },
						{ label: 'China', value: 'CN' },
						{ label: 'France', value: 'FR' },
						{ label: 'Germany', value: 'DE' },
						{ label: 'India', value: 'IN' },
						{ label: 'Indonesia', value: 'ID' },
						{ label: 'Italy', value: 'IT' },
						{ label: 'Japan', value: 'JP' },
						{ label: 'Mexico', value: 'MX' },
						{ label: 'Netherlands', value: 'NL' },
						{ label: 'Russia', value: 'RU' },
						{ label: 'South Korea', value: 'KR' },
						{ label: 'Spain', value: 'ES' },
						{ label: 'Turkey', value: 'TR' },
						{ label: 'United Kingdom', value: 'UK' },
						{ label: 'United States', value: 'US' },
					] }
				/>
				{phone ? "" :
				<Button
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add an phone +", "Direction to add an phone followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={phone ? "" : "tec-events-onboarding__form-field--hidden" }
					id="venue-phone"
					label={__("phone", "the-events-calendar")}
					onChange={setPhone}
					defaultValue={phone}
					disabled={disabled}
					type="phone"
				/>
				{website ? "" :
				<Button
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a website +", "Direction to add a website followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className={website ? "" : "tec-events-onboarding__form-field--hidden" }
					id="venue-website"
					label={__("Website", "the-events-calendar")}
					onChange={setWebsite}
					defaultValue={website}
					disabled={disabled}
					type="url"
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab} disabled={false}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default VenueContent;
