import React from "react";
import { __ } from '@wordpress/i18n';
import { TextControl, SelectControl, Button } from '@wordpress/components';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import VenueIcon from './img/venue';
import { _x } from '@wordpress/i18n';

const VenueContent = ({closeModal, moveToNextTab, skipToNextTab}) => {
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
			<VenueIcon />
			<h1 className="tec-events-onboarding__tab-header">{__("Add your first event venue.", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__("Show your attendees where they need to go to get to your events. You can display the location using Google Maps on your event pages.", "the-events-calendar")}</p>
			<div className="tec-events-onboarding__form-wrapper">
				<TextControl
					__nextHasNoMarginBottom
					label={__("Venue Name", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value=""
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Address", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value=""
					type="text"
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("State or province", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value=""
					type="text"
				/>
				<TextControl
					__nextHasNoMarginBottom
					label={__("Zip / Postal code", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					value=""
					type="text"
				/>
				<SelectControl
					__nextHasNoMarginBottom
					defaultValue="US"
					label={__("Country", "the-events-calendar")}
					onChange={function noRefCheck(){}}
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
					value=""
				/>
				<Button
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add a website +", "Direction to add a website followed by a plus sign", "the-events-calendar")}
				</Button>
				<TextControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field--hidden"
					id="venue-website"
					label={__("Website", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="url"
					value=""
				/>
				<Button
					onClick={showField}
					variant="tertiary"
				>
					{_x("Add an email +", "Direction to add an email followed by a plus sign", "the-events-calendar")}
				</Button>
				<TextControl
					__nextHasNoMarginBottom
					className="tec-events-onboarding__form-field--hidden"
					id="venue-email"
					label={__("Email", "the-events-calendar")}
					onChange={function noRefCheck(){}}
					type="email"
					value=""
				/>
			</div>
			 <p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab}/></p>
			 <p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default VenueContent;
