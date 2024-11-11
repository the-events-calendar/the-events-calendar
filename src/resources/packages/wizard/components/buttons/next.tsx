import React from "react";
import { Button } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { SETTINGS_STORE_KEY } from "../../data";
import { API_ENDPOINT } from "../../data/settings/constants";

const NextButton = ({ disabled, moveToNextTab, tabSettings }) => {
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
	}, [isClicked,]);

	return (
		<Button
			variant="primary"
			disabled={disabled || isSaving}
			onClick={() => setClicked(true)}
		>
			{__('Continue', 'the-events-calendar')}
		</Button>
	);
};

export default NextButton;
