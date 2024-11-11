import React from "react";
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import {useState, useEffect} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { API_ENDPOINT } from "../../data/settings/constants";
import {SETTINGS_STORE_KEY} from "../../data";

const SetupButton = ({ tabSettings, moveToNextTab }) => {
	const settings = useSelect(select => select(SETTINGS_STORE_KEY).getSettings() || {}, []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const [isClicked, setClicked] = useState(false);
	const [isSaving, setSaving] = useState(false);

	useEffect(() => {
		const handleTabChange = async () => {
			setSaving(true);

			// Dynamically update settings for the current tab
			if (tabSettings) {
				updateSettings(tabSettings);
			}

			// Filter settings and make the API call
			const { timezones, availableViews, countries, ...filteredSettings } = settings;
			const result = await apiFetch({
				method: "POST",
				data: filteredSettings,
				path: API_ENDPOINT,
			});

			if (result.success) {
				// Move to the next tab if API call was successful
				moveToNextTab();
			} else {
				console.error("Failed to save settings.");
			}

			setSaving(false);
		};

		if (isClicked) {
			handleTabChange();
		}
	}, [isClicked]);


	return (
		<Button
			variant="primary"
			onClick={setClicked}
			disabled={isSaving}
		>
			{__("Set up my calendar", "the-events-calendar")}
		</Button>
	);
};

export default SetupButton;
