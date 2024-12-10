import React from "react";
import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ViewCheckbox = ({ view, isChecked, onChange, icon }) => {
	const viewLabel = view === 'all' ? __( 'Select all the views', 'the-events-calendar' ) : view.charAt(0).toUpperCase() + view.slice(1);
	return (
		<div
			id={`tec-events-onboarding__checkbox-${view}`}
			className="tec-events-onboarding__checkbox tec-events-onboarding__checkbox--view"
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				aria-describedby={`tec-events-onboarding__checkbox-label-${view}`}
				checked={isChecked}
				onChange={(isChecked) => onChange(view, isChecked)} // Pass the view and new checked state to parent
				id={`tec-events-onboarding__checkbox-input-${view}`}
				className="tec-events-onboarding__checkbox-input"
				value={view}
			/>
			<div>
				<label
					id={`tec-events-onboarding__checkbox-label-${view}`}
					htmlFor={`tec-events-onboarding__checkbox-input-${view}`}
					className={isChecked ? "tec-events-onboarding__checkbox-label tec-events-onboarding__checkbox-label--checked" : "tec-events-onboarding__checkbox-label"}
				>
					{icon}
					{viewLabel}
				</label>
			</div>
		</div>
	);
};

export default ViewCheckbox;
