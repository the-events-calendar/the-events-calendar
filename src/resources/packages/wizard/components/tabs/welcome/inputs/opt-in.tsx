import React from 'react';
import {CheckboxControl} from '@wordpress/components';
import {useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';

const OptInCheckbox = ({ initialOptin, onChange }) => {
	const [ isChecked, setChecked ] = useState( initialOptin );

	const handleChange = (newCheckedState) => {
		setChecked(newCheckedState);
		onChange(newCheckedState); // Call the onChange callback passed from the parent
	};

	return (
		<div className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--optin">
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby="tec-events-onboarding__checkbox-description"
				checked={isChecked}
				onChange={handleChange}
				id="tec-events-onboarding__optin-checkbox-input"
			/>
			<div className="tec-events-onboarding__checkbox-description">
				<label htmlFor="tec-events-onboarding__optin-checkbox-input">
				{__("Yes, I’d like to share basic information and have access to the TEC chatbot.", "the-events-calendar")}
				</label>
				<div
					id="tec-events-onboarding__checkbox-description"
				>
				<a href="#" target="_blank">{__("What permissions are being granted?", "the-events-calendar")}</a>
				</div>
			</div>
		</div>
	);
};

export default OptInCheckbox;
