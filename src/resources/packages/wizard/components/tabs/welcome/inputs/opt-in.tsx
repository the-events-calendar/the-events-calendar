import React from 'react';
import {CheckboxControl} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';

const OptInCheckbox = ({initialOptin}) => {
	// Don't show the checkbox if they've already opted in.
	if(initialOptin) {
		return;
	}

	const [ isChecked, setChecked ] = useState( initialOptin );
	return (
		<div
			alignment="top"
			justify="center"
			spacing={0}
			className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--optin"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby="tec-events-onboarding__checkbox-description"
				checked={isChecked}
				onChange={setChecked}
				id="tec-events-onboarding__optin-checkbox-input"
			/>
			<div className="tec-events-onboarding__checkbox-description">
				<label htmlFor="tec-events-onboarding__optin-checkbox-input">
				{__("Yes, I’d like to share basic information and have access to the TEC chatbot.", "the-events-calendar")}
				</label>
				<div
					id="tec-events-onboarding__checkbox-description"
				>
				<a href="#" target="_blank">What permissions are being granted?</a>
				</div>
			</div>
		</div>
	);
};

export default OptInCheckbox;
