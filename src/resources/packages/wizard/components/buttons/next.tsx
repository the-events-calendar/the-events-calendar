import React from "react";
import { Button } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { SETTINGS_STORE_KEY } from "../../data";
import { API_ENDPOINT } from "../../data/settings/constants";

const NextButton = ({ disabled, moveToNextTab, tabSettings }) => {
	const actionNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("action_nonce"), []);
	const wpNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("_wpnonce"), []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const isSaving = useSelect(select => select(SETTINGS_STORE_KEY).getIsSaving() || false, []);
	const setSaving = useDispatch(SETTINGS_STORE_KEY).setSaving;
	const [isClicked, setClicked] = useState(false);

	// Reset isSaving state when any field in tabSettings changes
	useEffect(() => {
		if (tabSettings) {
			// If the user changes any field, we reset the saving state
			setSaving(false);
		}
	}, [tabSettings]);

	useEffect(() => {
		const handleTabChange = async () => {
			setSaving(true);

			// Dynamically update settings for the current tab
			if (tabSettings) {
				updateSettings(tabSettings);
			}

			// Filter settings and make the API call
			const { timezones, availableViews, countries, ...filteredSettings } = tabSettings;
			filteredSettings.action_nonce = actionNonce;

			apiFetch.use( apiFetch.createNonceMiddleware( wpNonce ) );

			const result = await apiFetch({
				method: "POST",
				data: filteredSettings,
				path: API_ENDPOINT,
			});

			if (result.success) {
				// Move to the next tab if API call was successful
				moveToNextTab();
			} else {
				// Optionally, handle error feedback here.
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
