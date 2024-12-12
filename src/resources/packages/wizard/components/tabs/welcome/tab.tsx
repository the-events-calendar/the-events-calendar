import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from "@wordpress/data";
import SetupButton from '../../buttons/setup';
import ExitButton from '../../buttons/exit';
import OptInCheckbox from './inputs/opt-in';
import Illustration from './img/wizard-welcome-img.png';
import { SETTINGS_STORE_KEY } from "../../../data";

const WelcomeContent = ({moveToNextTab, skipToNextTab}) => {
	const optin = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('optin') || false, []);
	const [originalValue, setOriginalValue] = useState(optin);
	const [optinValue, setOptinValue] = useState(optin); // Store the updated optin value

	useEffect(() => {
		// Update the local state if the optin value changes
		setOptinValue(optin);
	}, [optin]);

	// Create tabSettings object to pass to NextButton
	const tabSettings = {
		optin: optinValue, // Include updated optin value
		currentTab: 0, // Include the current tab index.
		begun: true, // Indicate that the user has started the wizard.
	};

	return (
		<>
			<div className="tec-events-onboarding__content-checkbox-grid">
			<div className="tec-events-onboarding__content-header-grid">
				<img src={Illustration} className="tec-events-onboarding__welcome-header" alt="Welcome" role="presentation" />
				<div className="tec-events-onboarding__content-grid">
				<h1 className="tec-events-onboarding__tab-header">{__("Welcome to The Events Calendar", "the-events-calendar")}</h1>
				<p className="tec-events-onboarding__tab-subheader">{__("Congratulations on installing the best event management solution for WordPress. Let’s tailor your experience to your needs.", "the-events-calendar")}</p>
				<p>
					<SetupButton
					tabSettings={tabSettings}
					moveToNextTab={moveToNextTab}
					/>
				</p>
				<p>
					<ExitButton />
				</p>
				</div>
			</div>
			{!originalValue && <OptInCheckbox initialOptin={optin} onChange={setOptinValue} />}
			</div>
		</>
	);
};

export default WelcomeContent;
