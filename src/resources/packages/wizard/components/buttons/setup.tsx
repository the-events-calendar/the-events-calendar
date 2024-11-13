import React from "react";
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import {useState, useEffect} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { API_ENDPOINT } from "../../data/settings/constants";
import {SETTINGS_STORE_KEY} from "../../data";

const SetupButton = ({ tabSettings, moveToNextTab }) => {
	const actionNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("action_nonce"), []);
	const wpNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("_wpnonce"), []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const isSaving = useSelect(select => select(SETTINGS_STORE_KEY).getIsSaving() || false, []);
	const setSaving = useDispatch(SETTINGS_STORE_KEY).setSaving;
	const [isClicked, setClicked] = useState(false);

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
			onClick={setClicked}
			disabled={isSaving}
		>
			{__("Set up my calendar", "the-events-calendar")}
		</Button>
	);
};

export default SetupButton;
