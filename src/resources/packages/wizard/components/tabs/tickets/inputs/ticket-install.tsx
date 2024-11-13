import React from "react";
import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const TicketInstallCheckbox = (onChange) => {
	const [ isChecked, setChecked ] = useState( false );

	const handleChange = (isChecked) => {
        setChecked(isChecked);
		onChange(isChecked); // Call the onChange callback passed from the parent
    };

	return (
		<div
			alignment="top"
			justify="center"
			spacing={0}
			className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--tickets"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby="tec-events-onboarding__checkbox-description"
				checked={isChecked}
				onChange={handleChange}
				id="tec-events-onboarding__tickets-checkbox-input"
			/>
			<div className="tec-events-onboarding__checkbox-description">
				<label htmlFor="tec-events-onboarding__tickets-checkbox-input">
					{__("Yes, install Event Tickets for free on my website.", "the-events-calendar")}
				</label>
				<div
					id="tec-events-onboarding__checkbox-description"
				>
				</div>
			</div>
		</div>
	);
};

export default TicketInstallCheckbox;
