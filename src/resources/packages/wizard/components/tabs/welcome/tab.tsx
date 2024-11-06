import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import SetupButton from '../../buttons/setup';
import ExitButton from '../../buttons/exit';
import OptInCheckbox from './inputs/opt-in';
import Illustration from './img/wizard-welcome-img.png';

const WelcomeContent = ({closeModal, moveToNextTab}) => {
	const optinRef = React.useRef();
	const optin = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('optin') || false, []);
	const { updateSetting } = useDispatch(SETTINGS_STORE_KEY);

	// Function to handle the "continue" button click
	const handleContinue = () => {
		// Get checked state from OptInCheckbox component.
		const checked = optinRef.current?.isChecked() || false;

		// Update the optin setting in the store.
		updateSetting({ key: 'optin', value: checked });

		// Now move to the next tab.
		moveToNextTab();
	};

	return (
		<>
			<div className='tec-events-onboarding__content-checkbox-grid'>
				<div className="tec-events-onboarding__content-header-grid">
					<img src={Illustration} className="tec-events-onboarding__welcome-header" alt="Welcome" role="presentation" />
					<div className="tec-events-onboarding__content-grid">
						<h1 className="tec-events-onboarding__tab-header">{__("Welcome to The Events Calendar", "the-events-calendar")}</h1>
						<p className="tec-events-onboarding__tab-subheader">{__("Congratulations on installing the best event management solution for WordPress. Let’s tailor your experience to your needs.", "the-events-calendar")}</p>
						<p><SetupButton moveToNextTab={handleContinue}/></p>
						<p><ExitButton closeModal={closeModal} /></p>
					</div>
				</div>
				{ !optin && ( <OptInCheckbox initialOptin={optin} ref={optinRef}  /> ) }
			</div>
		</>
	);
};

export default WelcomeContent;
