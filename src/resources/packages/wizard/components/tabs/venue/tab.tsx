import React from "react";
import { __, _x } from '@wordpress/i18n';
import { TextControl, SelectControl, Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import VenueIcon from './img/venue';

interface Venue {
	id: number;
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
		|| {id: 0,  name: "", address: "", city: "", state: "", zip: "", country: "", phone: "", website: "", }, []);
	const countries = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("countries"), []);

	// Check if any fields are filled.
	const disabled = !!venue.name || !!venue.address || !!venue.city || !!venue.state || !!venue.zip || !!venue.country || !!venue.phone || !!venue.website;
	const [id, setId] = useState(venue.id || 0);
	const [name, setName] = useState(venue.name || "");
	const [address, setAddress] = useState(venue.address || "");
	const [city, setCity] = useState(venue.city || "");
	const [state, setState] = useState(venue.state || "");
	const [zip, setZip] = useState(venue.zip || "");
	const [country, setCountry] = useState(venue.country || "US");
	const [phone, setPhone] = useState(venue.phone || "");
	const [website, setWebsite] = useState(venue.website || "");
	const [showWebsite, setShowWebsite] = useState(false);
	const [showPhone, setShowPhone] = useState(false);
	const [canContinue, setCanContinue] = useState(false);


	// Compute whether the "Continue" button should be enabled
    useEffect(() => {
        const fieldsToCheck = {
            'venue-name': isValidName(),
			'venue-address': isValidAddress(),
			'venue-city': isValidCity(),
			'venue-state': isValidState(),
			'venue-zip': isValidZip(),
			'venue-country': isValidCountry(),
            'venue-phone': isValidPhone(),
            'venue-website': isValidWebsite(),
		};
		setCanContinue(Object.values(fieldsToCheck).every((field) => !!field));
    }, [name, address, city, state, zip, phone, website, showPhone, showWebsite ]);

	/**
	 * Function to show hidden fields.
	 */
	const showField = (event) => {
		const ele = event.target;
		ele.nextSibling.classList.remove("tec-events-onboarding__form-field--hidden");
		ele.style.display = "none";
	}

	const isValidName = () => {
		const isValid = !!name;
		const ele = document.getElementById("venue-name");

		if (!isValid) {
			ele?.classList.add("invalid");
		} else {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidAddress = () => {
		const isValid = !!address;
		const ele = document.getElementById("venue-address");

		if (!isValid) {
			ele?.classList.add("invalid");
		} else {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidCity = () => {
		const isValid = !!city;
		const ele = document.getElementById("venue-city");

		if (!isValid) {
			ele?.classList.add("invalid");
		} else {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidState = () => {
		const isValid = !!state;
		const ele = document.getElementById("venue-state");

		if (!isValid) {
			ele?.classList.add("invalid");
		} else {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidZip = () => {
		const zipPattern = /^[a-z0-9][a-z0-9\- ]{0,10}[a-z0-9]$/i;
		const isValid = !!zip && zipPattern.test(zip);
		const ele = document.getElementById("venue-zip");

		if (!isValid) {
			ele?.classList.add("invalid");
		} else {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidCountry = () => {
		const isValid = !!country;
		const ele = document.getElementById("venue-country");

		if (!isValid) {
			ele?.classList.add("invalid");
		} else {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidPhone = () => {
		const phonePattern = /^\+?\d?[\s.-]?(?:\(\d{3}\)|\d{3})[\s.-]?\d{3}[\s.-]?\d{4}$/;
		const isValid = !showPhone || ( !!phone && phonePattern.test(phone) );
		const ele = document.getElementById("venue-phone");

		if (showPhone && !isValid) {
			ele?.classList.add("invalid");
		} else if (showPhone) {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	const isValidWebsite = () => {
		const websitePattern = /^(http|https):\/\/[^ "]+?$/;
		const isValid = !showWebsite || ( !!website && websitePattern.test(website) );
		const ele = document.getElementById("venue-website");

		if (showWebsite && !isValid) {
			ele?.classList.add("invalid");
		} else if (showWebsite) {
			ele?.classList.remove("invalid");
		}

		return isValid;
	}

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		venue: {
			id,
			name,
			address,
			city,
			state,
			zip,
			country,
			phone,
			website,
		},
		currentTab: 4, // Include the current tab index.
	};

	const subHeaderText = id > 0 ?
		__("Looks like you have already created your first venue. Well done!", "the-events-calendar") :
		__("Show your attendees where they need to go to get to your events. You can display the location using Google Maps on your event pages.", "the-events-calendar");

	return (
		<>
			<VenueIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Add your first event venue", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{subHeaderText}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__("Venue Name", "the-events-calendar")}
					id="venue-name"
					onChange={setName}
					defaultValue={name}
					disabled={disabled}
					placeholder={__("Enter venue name", "the-events-calendar")}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Address", "the-events-calendar")}
					id="venue-address"
					onChange={setAddress}
					defaultValue={address}
					disabled={disabled}
					type="text"
					placeholder={__("Enter venue street address", "the-events-calendar")}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("City", "the-events-calendar")}
					id="venue-city"
					onChange={setCity}
					defaultValue={city}
					disabled={disabled}
					type="text"
					placeholder={__("Enter city", "the-events-calendar")}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("State or province", "the-events-calendar")}
					id="venue-state"
					onChange={setState}
					defaultValue={state}
					disabled={disabled}
					type="text"
					placeholder={__("Enter state or province", "the-events-calendar")}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Zip / Postal code", "the-events-calendar")}
					id="venue-zip"
					onChange={setZip}
					defaultValue={zip}
					disabled={disabled}
					type="text"
					placeholder={__("Enter zip or postal code", "the-events-calendar")}
				/>
				<SelectControl
					__nextHasNoMarginBottom
					label={__("Country", "the-events-calendar")}
					id="venue-country"
					onChange={setCountry}
					defaultValue={country}
					disabled={disabled}>
					{Object.entries(countries).map(([key, continents]) => (
						<optgroup key={key} className="continent" label={key}>
							{Object.entries(continents as {[key: string]: string}).map(([key, country]) => (
								<option key={key}  value={key}>{country}</option>
							))}
						</optgroup>
					))}
				</SelectControl>
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
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab} tabSettings={tabSettings} disabled={false}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab} currentTab={4}/></p>
		</>
	);
};

export default VenueContent;
