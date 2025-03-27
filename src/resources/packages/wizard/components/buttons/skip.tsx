import React from "react";
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from "@wordpress/data";
import { useEffect, useState } from '@wordpress/element';
import { MODAL_STORE_KEY, SETTINGS_STORE_KEY } from "../../data";
import { API_ENDPOINT } from "../../data/settings/constants";

const SkipButton = ({skipToNextTab, currentTab}) => {
	const skipTab = useDispatch(SETTINGS_STORE_KEY).skipTab;
	const closeModal = useDispatch(MODAL_STORE_KEY).closeModal;

	const getSettings = useSelect(select => select(SETTINGS_STORE_KEY).getSettings);
	const getCompletedTabs = useSelect(select => select(SETTINGS_STORE_KEY).getCompletedTabs);
	const getSkippedTabs = useSelect(select => select(SETTINGS_STORE_KEY).getSkippedTabs);
	const getVisitedFields = useSelect(SETTINGS_STORE_KEY).getVisitedFields;

	const [isClicked, setClicked] = useState(false);

	useEffect(() => {
		const handleSkipWizard = async () => {
			// Mark tab as skipped.
			skipTab(currentTab);

			const settings = getSettings();

			if ( currentTab === 5) {
				settings.finished = true;
			}

			const result = await apiFetch({
				method: "POST",
				data: {
					...settings, // Add settings data
					completedTabs: getCompletedTabs(), // Include completedTabs
					skippedTabs: getSkippedTabs(),     // Include skippedTabs
					visitedFields: getVisitedFields(), // Include visitedFields
				},
				path: API_ENDPOINT,
			});

			if (result.success) {
				if ( currentTab < 5) {
					skipToNextTab();
				} else {
					setTimeout(() => {
						closeModal();
					}, 1000);
				}
			} else {
				// Handle error - close modal.
				setTimeout(() => {
					closeModal();
				}, 1000);
			}

		};

		if (isClicked) {
			handleSkipWizard();
		}
	}, [isClicked]);

	return (
		<Button
			variant="tertiary"
			onClick={() => setClicked(true)}
			className="tec-events-onboarding__button tec-events-onboarding__button--skip"
		>
			{__( "Skip step", "the-events-calendar" )}
		</Button>
	);
};

export default SkipButton;
