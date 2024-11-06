import React, { useEffect, useState } from "react";
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY } from "../../../data";
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import ViewCheckbox from './inputs/view-checkbox';

interface DisplayContentProps {
	closeModal: () => void;
	moveToNextTab: () => void;
	skipToNextTab: () => void;
}

const DisplayContent: React.FC<DisplayContentProps> = ({ closeModal, moveToNextTab, skipToNextTab }) => {
	const availableViews = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('availableViews') || [], []);
	const activeViews = useSelect(select => select(SETTINGS_STORE_KEY).getSetting('activeViews') || [], []);
	const { updateSetting } = useDispatch(SETTINGS_STORE_KEY);

	console.log(activeViews);

	// Ensure "all" option is always included
	if (!availableViews.includes('all')) {
		availableViews.push('all');
	}

	// Track which views are checked
	const [checkedViews, setCheckedViews] = useState<string[]>(activeViews);

	// Sync `checkedViews` state with `activeViews` from store on load and when `activeViews` changes
	useEffect(() => {
		setCheckedViews(activeViews);
	}, [activeViews]);

	// Update the checked state when a checkbox changes
	const handleCheckboxChange = (view: string, isChecked: boolean) => {
		setCheckedViews((prevChecked) =>
			isChecked ? [...prevChecked, view] : prevChecked.filter((v) => v !== view)
		);
	};

	// Save the checked views to the store on "Continue" button click
	const handleContinue = () => {
		const filteredViews = checkedViews.filter(view => view !== 'all');
		console.log('Saving the following views: ', filteredViews);
		updateSetting({key:'activeViews', value:filteredViews});
		moveToNextTab();
	};

	// Check if any checkboxes are selected
	const isAnyChecked = checkedViews.length > 0;

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
					<ViewCheckbox
						key={key}
						view={view}
						isChecked={checkedViews.includes(view)} // Pass the checked state to each checkbox
						onChange={handleCheckboxChange} // Pass the parent handler
					/>
				))}
			</div>
			<p className="tec-events-onboarding__element--center">
				<NextButton moveToNextTab={handleContinue} disabled={!isAnyChecked} />
			</p>
			<p className="tec-events-onboarding__element--center">
				<SkipButton skipToNextTab={skipToNextTab} />
			</p>
		</>
	);
};

export default DisplayContent;
