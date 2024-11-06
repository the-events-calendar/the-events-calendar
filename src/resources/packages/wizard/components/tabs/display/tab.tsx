import React, { useEffect, useState } from "react";
import { __ } from '@wordpress/i18n';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import ViewCheckbox from './inputs/view-checkbox';
import { useSelect } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";

interface DisplayContentProps {
	closeModal: () => void;
	moveToNextTab: () => void;
	skipToNextTab: () => void;
}

const DisplayContent: React.FC<DisplayContentProps> = ({ closeModal, moveToNextTab, skipToNextTab }) => {
	const [isAnyChecked, setIsAnyChecked] = useState(false); // State to track if any checkbox is checked
	const availableViews = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('availableViews') || false, []);
	const activeViews = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('activeViews') || false, []);

	useEffect(() => {
		// Function to check if any checkbox is checked
		const updateAnyChecked = () => {
			const anyChecked = availableViews.some((view) => activeViews.includes(view));
			setIsAnyChecked(anyChecked);
		};

		// Check on initial load based on activeViews
		updateAnyChecked();

		const handleCheckboxChange = (event: Event) => {
			const target = event.target as HTMLInputElement;

			// Check if the event target matches the input selector
			if (!target.matches('.tec-events-onboarding__checkbox-input .components-checkbox-control__input')) return;

			const isChecked = target.checked;
			const isAll = target.value === 'all';

			// Helper function to toggle checkbox states and label classes
			const toggleCheckboxes = (checked: boolean) => {
				document.querySelectorAll('.tec-events-onboarding__checkbox-input input').forEach((checkbox) => {
					(checkbox as HTMLInputElement).checked = checked;
				});
				document.querySelectorAll('.tec-events-onboarding__checkbox-label').forEach((label) => {
					label.classList.toggle('tec-events-onboarding__checkbox-label--checked', checked);
				});
			};

			if (isAll && isChecked) {
				toggleCheckboxes(true);
			} else if (!isChecked) {
				// Uncheck the "all" checkbox and remove the checked class from specific label
				const allCheckbox = document.getElementById('tec-events-onboarding__checkbox-input-all') as HTMLInputElement;
				allCheckbox && (allCheckbox.checked = false);

				const allLabel = document.getElementById('tec-events-onboarding__checkbox-label-all');
				allLabel?.classList.remove('tec-events-onboarding__checkbox-label--checked');

				const label = document.getElementById(`tec-events-onboarding__checkbox-label-${target.value}`);
				label?.classList.remove('tec-events-onboarding__checkbox-label--checked');
			}

			// Update the isAnyChecked state based on the checkbox states
			updateAnyChecked();
		};

		// Attach event listener on mount
		document.addEventListener('change', handleCheckboxChange);

		// Clean up event listener on unmount
		return () => document.removeEventListener('change', handleCheckboxChange);
	}, [availableViews, activeViews]); // Add availableViews and activeViews as dependencies

	return (
		<>
			<h1 className="tec-events-onboarding__tab-header">
				{__("How do you want people to view your calendar?", "the-events-calendar")}
			</h1>
			<p className="tec-events-onboarding__tab-subheader">
				{__("Select how you want to display your events on your site. You can choose more than one.", "the-events-calendar")}
			</p>
			<div className="tec-events-onboarding__grid--view-checkbox">
				{availableViews.map((view, key) => (
					<ViewCheckbox view={view} checked={activeViews.includes(view)} key={key} />
				))}
			</div>
			<p className="tec-events-onboarding__element--center">
				<NextButton moveToNextTab={moveToNextTab} disabled={!isAnyChecked} /> {/* Pass disabled state */}
			</p>
			<p className="tec-events-onboarding__element--center">
				<SkipButton skipToNextTab={skipToNextTab} />
			</p>
		</>
	);
};

export default DisplayContent;
