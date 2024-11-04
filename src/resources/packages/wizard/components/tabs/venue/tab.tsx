import React from "react";
import { __, sprintf } from '@wordpress/i18n';
import { TextControl, SelectControl, Button } from '@wordpress/components';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import VenueIcon from './img/venue';
import { _x } from '@wordpress/i18n';

const VenueContent = ({closeModal, moveToNextTab, skipToNextTab, bootData}) => {
	const {venue} = bootData;
	const disabled = !! venue;

	// Mocking data for now.
	const venueObj = venue ? {
		name: "The Events Calendar",
		address: "1600 Pennsylvania Ave NW",
		city: "Washington",
		state: "DC",
		zip: "20500",
		country: "US",
		website: "https://theeventscalendar.com",
		email: "venue@example.com",
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

	const header = venue ?
		<h1 className="tec-events-onboarding__tab-header">{__("Sweet - you've already got a venue set up!", "the-events-calendar")}</h1>
		: <h1 className="tec-events-onboarding__tab-header">{__("Add your first event venue.", "the-events-calendar")}</h1>;
	const subheader = venue ?
		<p className="tec-events-onboarding__tab-subheader">{
			sprintf(
				/* Translators: %s are opening/closing anchor tags to edit the venue */
				__('You can just skip right along - click here if you want to %sedit the venue%s.', "the-events-calendar"),
				'<a href="#">',
				'</a>'
			)
		}</p>
		: <p className="tec-events-onboarding__tab-subheader">{__("Show your attendees where they need to go to get to your events. You can display the location using Google Maps on your event pages.", "the-events-calendar")}</p>;
	return (
		<>
			<VenueIcon />
			{header}
			{subheader}
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__("Venue Name", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value={venueObj && venueObj.name ? venueObj.name : ""}
					disabled={disabled}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Address", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value={venueObj && venueObj.address ? venueObj.address : ""}
					disabled={disabled}
					type="text"
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("State or province", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value={venueObj && venueObj.state ? venueObj.state : ""}
					type="text"
					disabled={disabled}
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Zip / Postal code", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value={venueObj && venueObj.zip ? venueObj.zip : ""}
					type="text"
					disabled={disabled}
				/>
				<SelectControl
					__nextHasNoMarginBottom
					value={venueObj && venueObj.country ? venueObj.country : "US"}
					label={__("Country", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					disabled={disabled}
					options={ [
						// THese don't need translations - they need to come from somewhere else - WP core has a list I believe.
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
				<TextControl
					__nextHasNoMarginBottom
					label={__("City", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="text"
					value={venueObj && venueObj.city ? venueObj.city : ""}
					disabled={disabled}
				/>
				{venueObj && venueObj.website ? "" :
				<Button
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a website +", "Direction to add a website followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field--hidden"
					id="venue-website"
					label={__("Website", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="url"
					value={venueObj && venueObj.website ? venueObj.website : ""}
					disabled={disabled}
				/>
				{venueObj&& venueObj.email ? "" :
				<Button
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add an email +", "Direction to add an email followed by a plus sign", "the-events-calendar")}
				</Button>}
				<TextControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field--hidden"
					id="venue-email"
					label={__("Email", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="email"
					value={venueObj && venueObj.email ? venueObj.email : ""}
					disabled={disabled}
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default VenueContent;
