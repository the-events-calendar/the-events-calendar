import React from "react";
import { __, _x } from '@wordpress/i18n';
import { BaseControl, Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import VenueIcon from './img/venue';

interface Venue {
	venueId: number;
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
	const venue: Venue = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('venue')
		|| {id: 0,  name: '', address: '', city: '', state: '', zip: '', country: '', phone: '', website: '', }, []);
	const countries = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('countries'), []);
	const visitedFields = useSelect(select => select(SETTINGS_STORE_KEY).getVisitedFields());
	const setVisitedField = useDispatch(SETTINGS_STORE_KEY).setVisitedField;

	// Check if any fields are filled.
	const disabled = !!venue.venueId;
	const [venueId, setId] = useState(venue.venueId || false);
	const [name, setName] = useState(venue.name || '');
	const [address, setAddress] = useState(venue.address || '');
	const [city, setCity] = useState(venue.city || '');
	const [state, setState] = useState(venue.state || '');
	const [zip, setZip] = useState(venue.zip || '');
	const [country, setCountry] = useState(venue.country || 'US');
	const [phone, setPhone] = useState(venue.phone || '');
	const [website, setWebsite] = useState(venue.website || '');
	const [showWebsite, setShowWebsite] = useState(!!venue.venueId ||!!venue.website || false);
	const [showPhone, setShowPhone] = useState(!!venue.venueId || !!venue.phone || false);
	const [canContinue, setCanContinue] = useState(false);

	// Compute whether the "Continue" button should be enabled
    useEffect(() => {
		if (venueId) {
			// If organizerId is set, bypass the check and enable "Continue"
			setCanContinue(true);
			return;
		}

        const fieldsToCheck = {
            'venue-name': isValidName(),
			'venue-address': isValidAddress(),
			'venue-city': isValidCity(),
			'venue-state': isValidState(),
			'venue-zip': isValidZip(),
			'venue-country': isValidCountry(),
            'venue-phone': isValidPhone(),
            'venue-website': isValidWebsite(),
			'visit-at-least-one': hasVisitedHere(),
		};
		setCanContinue(Object.values(fieldsToCheck).every((field) => !!field));
    }, [visitedFields, name, address, city, state, zip, country, phone, website, showPhone, showWebsite]);

	const hasVisitedHere = () => {
		const fields = ['venue-name', 'venue-address', 'venue-city', 'venue-state', 'venue-zip', 'venue-country', 'venue-phone', 'venue-website'];
		return fields.some(field => visitedFields.includes(field));
	}


    useEffect(() => {
		// Define the event listener function
		const handleBlur = (event) => {
			setVisitedField(event.target.id);
		};

		const fields = document.getElementById('venuePanel')?.querySelectorAll('input, select, textarea');
		fields?.forEach((field) => {
			field.addEventListener('blur', handleBlur);
		});

		return () => {
			fields?.forEach((field) => {
				field.removeEventListener('blur', handleBlur);
			});
		};
	}, []);

	const isValidName = () => {
		const inputId = 'venue-name';
		const isVisited = visitedFields.includes(inputId);
		const isValid = !!name;
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(name, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidAddress = () => {
		// Accept empty field as valid.
		if (!address) {
			return true;
		}

		const inputId = 'venue-address';
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const isValid = !!address;
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(address, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidCity = () => {
		// Accept empty field as valid.
		if (!city) {
			return true;
		}

		const inputId = 'venue-city';
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const isValid = !!city;
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(city, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidState = () => {
		// Accept empty field as valid.
		if (!state) {
			return true;
		}

		const inputId = 'venue-state';
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const isValid = !!state;
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(state, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidZip = () => {
		// Accept empty field as valid.
		if (!zip) {
			return true;
		}

		const inputId = 'venue-zip';
		const zipPattern = /^[a-z0-9][a-z0-9\- ]{0,10}[a-z0-9]$/i;
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const isValid = !!zip && zipPattern.test(zip);
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(zip, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidCountry = () => {
		const inputId = 'venue-country';
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const isValid = !!country;
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(country, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidPhone = () => {
		// Accept empty field as valid.
		if (!phone) {
			return true;
		}

		const inputId = 'venue-phone';
		const phonePattern = /^\+?\d{1,3}[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,4}[\s.-]?\d{1,4}$/;
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const isValid = !showPhone || (!!phone && phonePattern.test(phone));
		const fieldEle = document.getElementById(inputId);
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		if (isVisited) {
			toggleClasses(phone, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const isValidWebsite = () => {
		// Accept empty field as valid.
		if (!website) {
			return true;
		}

		const inputId = 'venue-website';
		const isVisited = visitedFields.includes(inputId);

		if (!isVisited) {
			return true;
		}

		const fieldEle = document.getElementById('venue-website');
		const parentEle = fieldEle?.closest('.tec-events-onboarding__form-field');

		let isValid = false;

		try {
			const url = new URL(website);
			isValid = url.protocol === 'http:' || url.protocol === 'https:';
		} catch (e) {
			isValid = false
		}

		if (isVisited) {
			toggleClasses(website, fieldEle, parentEle, isValid);
		}

		return isValid;
	}

	const toggleClasses = (field, fieldEle, parentEle, isValid) => {
		if (!field) {
			parentEle.classList.add('invalid', 'empty');
			fieldEle.classList.add('invalid');
		} else if (!isValid) {
			parentEle.classList.add('invalid');
			fieldEle.classList.add('invalid');
		} else {
			parentEle.classList.remove('invalid', 'empty');
			fieldEle.classList.remove('invalid');
		}
	}

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		venue: {
			venueId,
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

	const subHeaderText = venueId > 0 ?
		__('Looks like you have already created your first venue. Well done!', 'the-events-calendar') :
		__('Show your attendees where they need to go to get to your events. You can display the location using Google Maps on your event pages.', 'the-events-calendar');

	return (
		<>

			<VenueIcon />
			<div className="tec-events-onboarding__tab-header">
				<h1 className="tec-events-onboarding__tab-heading">{__('Add your first event venue', 'the-events-calendar')}</h1>
				<p className="tec-events-onboarding__tab-subheader">{subHeaderText}</p>
			</div>
			<div className="tec-events-onboarding__tab-content">
				<div className="tec-events-onboarding__form-wrapper">
					<BaseControl
						__nextHasNoMarginBottom
						label={__('Venue name', 'the-events-calendar')}
						id="venue-name"
						className="tec-events-onboarding__form-field"
					>
						<input
							id="venue-name"
							type="text"
							onChange={(e) => setName(e.target.value)}
							defaultValue={name}
							disabled={disabled}
							placeholder={__('Enter venue name', 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue name is required.', 'the-events-calendar')}</span>
					</BaseControl>
					<BaseControl
						__nextHasNoMarginBottom
						label={__('Address', 'the-events-calendar')}
						id="venue-address"
						className="tec-events-onboarding__form-field"
					>
						<input
							id="venue-address"
							type="text"
							onChange={(e) => setAddress(e.target.value)}
							defaultValue={address}
							disabled={disabled}
							placeholder={__('Enter venue street address', 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue address is required.', 'the-events-calendar')}</span>
						<span className="tec-events-onboarding__invalid-label">{__('Venue address is invalid.', 'the-events-calendar')}</span>
					</BaseControl>
					<BaseControl
						__nextHasNoMarginBottom
						label={__('City', 'the-events-calendar')}
						id="venue-city"
						className="tec-events-onboarding__form-field"
					>
						<input
							id="venue-city"
							type="text"
							onChange={(e) => setCity(e.target.value)}
							defaultValue={city}
							disabled={disabled}
							placeholder={__("Enter city", 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue city is required.', 'the-events-calendar')}</span>
						<span className="tec-events-onboarding__invalid-label">{__('Venue city is invalid.', 'the-events-calendar')}</span>
					</BaseControl>
					<BaseControl
						__nextHasNoMarginBottom
						label={__('State or province', 'the-events-calendar')}
						id="venue-state"
						className="tec-events-onboarding__form-field"
					>
						<input
							id="venue-state"
							onChange={(e) => setState(e.target.value)}
							defaultValue={state}
							disabled={disabled}
							type="text"
							placeholder={__('Enter state or province', 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue state is required.', 'the-events-calendar')}</span>
						<span className="tec-events-onboarding__invalid-label">{__('Venue state is invalid.', 'the-events-calendar')}</span>
					</BaseControl>
					<BaseControl
						__nextHasNoMarginBottom
						label={__('Zip / postal code', 'the-events-calendar')}
						id="venue-zip"
						className="tec-events-onboarding__form-field"
					>
						<input
							id="venue-zip"
							onChange={(e) => setZip(e.target.value)}
							defaultValue={zip}
							disabled={disabled}
							type="text"
							placeholder={__('Enter zip or postal code', 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue zip/postal code is required.', 'the-events-calendar')}</span>
						<span className="tec-events-onboarding__invalid-label">{__('Venue zip/postal code is invalid.', 'the-events-calendar')}</span>
					</BaseControl>
					<BaseControl
						__nextHasNoMarginBottom
						id="venue-country"
						className="tec-events-onboarding__form-field"
						label={__('Country', 'the-events-calendar')}
					>
						<select
							onChange={(e) => setCountry(e.target.value)}
							defaultValue={country}
							disabled={disabled}
							id="venue-country"
						>
							{Object.entries(countries).map(([key, continents]) => (
								<optgroup key={key} className="continent" label={key}>
									{Object.entries(continents as {[key: string]: string}).map(([key, country]) => (
										<option key={key}  value={key}>{country}</option>
									))}
								</optgroup>
							))}
						</select>
					</BaseControl>
					<span className="tec-events-onboarding__required-label">{__('Venue country is required.', 'the-events-calendar')}</span>
					<span className="tec-events-onboarding__invalid-label">{__('Venue country is invalid.', 'the-events-calendar')}</span>
					{!venueId && showPhone ? '' :
					<Button
						variant="tertiary"
						className="tec-events-onboarding__form-field-trigger"
						onClick={(event) => setShowPhone(true)}
					>
						{_x('Add a phone +', 'Direction to add an phone followed by a plus sign to indicate it shows a visually hidden field.', 'the-events-calendar')}
					</Button>}
					<BaseControl
						__nextHasNoMarginBottom
						className="tec-events-onboarding__form-field"
						id="venue-phone"
						label={__('Phone', 'the-events-calendar')}
					>
						<input
							id="venue-phone"
							onChange={(e) => setPhone(e.target.value)}
							defaultValue={phone}
							disabled={disabled}
							type="phone"
							placeholder={__('Enter phone number', 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue phone is required.', 'the-events-calendar')}</span>
						<span className="tec-events-onboarding__invalid-label">{__('Venue phone is invalid.', 'the-events-calendar')}</span>
					</BaseControl>
					{!venueId && showWebsite ? '' :
					<Button
						variant="tertiary"
						className="tec-events-onboarding__form-field-trigger"
						onClick={() => setShowWebsite(true)}
					>
						{_x('Add a website +', 'Direction to add a website followed by a plus sign to indicate it shows a visually hidden field.', 'the-events-calendar')}
					</Button>}
					<BaseControl
						__nextHasNoMarginBottom
						className="tec-events-onboarding__form-field"
						id="venue-website"
						label={__('Website', 'the-events-calendar')}
					>
						<input
							id="venue-website"
							onChange={(e) => setWebsite(e.target.value)}
							defaultValue={website}
							disabled={disabled}
							type="url"
							placeholder={__('Enter website', 'the-events-calendar')}
						/>
						<span className="tec-events-onboarding__required-label">{__('Venue website is required.', 'the-events-calendar')}</span>
						{website && !website.toLowerCase().startsWith("http") ? (
							<span className="tec-events-onboarding__invalid-label">
								{__('Venue website must start with a protocol, i.e. "https://"', 'the-events-calendar')}
							</span>
						) : (
							<span className="tec-events-onboarding__invalid-label">
								{__('Venue website is invalid.', 'the-events-calendar')}
							</span>
						)}

					</BaseControl>
				</div>
				<NextButton moveToNextTab={moveToNextTab} tabSettings={tabSettings} disabled={!canContinue}/>
				<SkipButton skipToNextTab={skipToNextTab} currentTab={4}/>
			</div>
		</>
	);
};

export default VenueContent;
