import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const TicketInstallCheckbox = () => {
	const [ isChecked, setChecked ] = useState( false );
	const handleTicketInstallChange = () => {}
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
				id="tec-events-onboarding__checkbox-input"
				onChange={handleTicketInstallChange}
			/>
			<div className="tec-events-onboarding__checkbox-description">
				<label htmlFor="tec-events-onboarding__checkbox-input">
				{__("Yes, install Event Tickets for free on my website.", "the-events-calendar")}
				</label>
				<div
					id="tec-events-onboarding__checkbox-description"
					style={{
						fontSize: 13
					}}
				>
				</div>
			</div>
		</div>
	);
};

export default TicketInstallCheckbox;
