import React from 'react';
import { __ } from '@wordpress/i18n';
import SetupButton from '../../buttons/setup';
import ExitButton from '../../buttons/exit';
import OptInCheckbox from './inputs/opt-in';
import Illustration from './img/wizard-welcome-img.png';

const WelcomeContent = ({closeModal, moveToNextTab, skipToNextTab, bootData}) => {
	const {optin} = bootData;

	return (
		<>
			<div className='tec-events-onboarding__content-checkbox-grid'>
				<div className="tec-events-onboarding__content-header-grid">
					<img src={Illustration} className="tec-events-onboarding__welcome-header" alt="Welcome" role="presentation" />
					<div className="tec-events-onboarding__content-grid">
						<h1 className="tec-events-onboarding__tab-header">{__("Welcome to The Events Calendar", "the-events-calendar")}</h1>
						<p className="tec-events-onboarding__tab-subheader">{__("Congratulations on installing the best event management solution for WordPress. Let’s tailor your experience to your needs.", "the-events-calendar")}</p>
						<p><SetupButton moveToNextTab={moveToNextTab}/></p>
						<p><ExitButton closeModal={closeModal} /></p>
					</div>
				</div>
				<OptInCheckbox initialOptin={optin} />
			</div>
		</>
	);
};

export default WelcomeContent;
