import { CheckboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const OptInCheckbox = () => {
	const [ isChecked, setChecked ] = useState( false );
	const handleOptInChange = () => {}
	return (
		<div
			alignment="top"
			justify="center"
			spacing={0}
			className="tec-events-onboarding__opt-in-checkbox--wrapper"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby="tec-events-opt-in-checkbox-description"
				checked={isChecked}
				id="tec-events-opt-in-checkbox"
				onChange={handleOptInChange}
			/>
			<div className="tec-events-onboarding__opt-in-checkbox--description">
				<label htmlFor="tec-events-opt-in-checkbox">
				Yes, I’d like to share basic information and have access to the TEC chatbot.
				</label>
				<div
					id="tec-events-opt-in-checkbox-description"
					style={{
						fontSize: 13
					}}
				>
				<a href="#" target="_blank">What permissions are being granted?</a>
				</div>
			</div>
		</div>
	);
};

export default OptInCheckbox;
