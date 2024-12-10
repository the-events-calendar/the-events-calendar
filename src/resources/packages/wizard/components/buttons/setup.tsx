import React from "react";
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import {useState, useEffect} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { API_ENDPOINT } from "../../data/settings/constants";
import {SETTINGS_STORE_KEY} from "../../data";

const SetupButton = ({ tabSettings, moveToNextTab }) => {
	const completeTab = useDispatch(SETTINGS_STORE_KEY).completeTab;
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;

	const wpNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("_wpnonce"), []);
	const getSettings = useSelect(select => select(SETTINGS_STORE_KEY).getSettings);
	const completedTabs = useSelect(select => select(SETTINGS_STORE_KEY).getCompletedTabs);
	const skippedTabs = useSelect(select => select(SETTINGS_STORE_KEY).getSkippedTabs);
	const getVisitedFields = useSelect(SETTINGS_STORE_KEY).getVisitedFields;

	const [isClicked, setClicked] = useState(false);

	useEffect(() => {
		const handleTabChange = async () => {
			// Mark the tab as completed.
			completeTab(tabSettings.currentTab);

			// Update settings Store for the current tab.
			updateSettings(tabSettings);

			// Add the wpnonce to the apiFetch middleware so we don't have to mess with it.
			apiFetch.use( apiFetch.createNonceMiddleware( wpNonce ) );

			const result = await apiFetch({
				method: "POST",
				data: {
					...getSettings(), // Add settings data
					completedTabs: completedTabs, // Include completedTabs
					skippedTabs: skippedTabs,     // Include skippedTabs
					visitedFields: getVisitedFields(), // Include visitedFields
				},
				path: API_ENDPOINT,
			});

			if (result.success) {
				// Move to the next tab.
				moveToNextTab();
			}
		};

		if (isClicked) {
			handleTabChange();
		}
	}, [isClicked,]);


	return (
		<Button
			variant="primary"
			onClick={setClicked}
			disabled={false}
		>
			{__("Set up my calendar", "the-events-calendar")}
		</Button>
	);
};

export default SetupButton;
