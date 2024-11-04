import React from "react";
import { __ } from '@wordpress/i18n';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import ViewCheckbox from './inputs/view-checkbox';

const DisplayContent = ({closeModal, moveToNextTab, skipToNextTab, bootData}) => {
	const { availableViews, activeViews } = bootData;
	console.log(availableViews);
	console.log(activeViews);

	document.addEventListener(
		'change',
		(event) => {
			const targetElement = event.target as HTMLInputElement;
			if ( targetElement.matches('.tec-events-onboarding__checkbox-input .components-checkbox-control__input') ) {
				const isChecked = targetElement.checked;
				const isAll = targetElement.value === 'all';

				if ( isAll && isChecked ) {
					document.querySelectorAll('.tec-events-onboarding__checkbox-input input').forEach((checkbox) => {
						(checkbox as HTMLInputElement).checked = true;
					});
					document.querySelectorAll('.tec-events-onboarding__checkbox-label').forEach((label) => {
						label.classList.add('tec-events-onboarding__checkbox-label--checked');
					});
				} else if ( ! isChecked ) {
					const allCheckbox = document.getElementById('tec-events-onboarding__checkbox-input-all') as HTMLInputElement;
					if (allCheckbox) {
						allCheckbox.checked = false;
					}

					const allLabel = document.getElementById('tec-events-onboarding__checkbox-label-all');
					if (allLabel) {
						allLabel.classList.remove('tec-events-onboarding__checkbox-label--checked');
					}

					const labelElement = document.getElementById(`tec-events-onboarding__checkbox-label-${targetElement.value}`);
					if (labelElement) {
						labelElement.classList.remove('tec-events-onboarding__checkbox-label--checked');
					}
				}

			}

			return true;
		}
	);

	return (
		<>
			<h1 className="tec-events-onboarding__tab-header">{__("How do you want people to view your calendar?", "the-events-calendar")}</h1>
			<p className="tec-events-onboarding__tab-subheader">{__("Select how you want to display your events on your site. You can choose more than one.", "the-events-calendar")}</p>
			<div className="tec-events-onboarding__grid--view-checkbox">
				{availableViews.map((view, key) => (
					<ViewCheckbox view={view} active={activeViews} available={availableViews} key={key} />
				))}
			</div>
			<p className="tec-events-onboarding__element--center"><NextButton moveToNextTab={moveToNextTab}/></p>
			<p className="tec-events-onboarding__element--center"><SkipButton skipToNextTab={skipToNextTab}/></p>
		</>
	);
};

export default DisplayContent;
