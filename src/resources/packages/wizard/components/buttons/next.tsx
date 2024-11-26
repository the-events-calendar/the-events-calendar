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
	const isSaving = useSelect(select => select(SETTINGS_STORE_KEY).getIsSaving() || false, []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const setSaving = useDispatch(SETTINGS_STORE_KEY).setSaving;
	const [isClicked, setClicked] = useState(false);

	// Reset isSaving state when any field in tabSettings changes
	useEffect(() => {
		if (tabSettings) {
			// If the user changes any field, we reset the saving state
			setSaving(false);
			// and the button clicked state.
			setClicked(false);
		}
	}, [tabSettings]);

	useEffect(() => {
		const handleTabChange = async () => {
			setSaving(true);

			// Add our action nonce.
			tabSettings.action_nonce = actionNonce;

			// Add the wpnonce to the apiFetch middleware so we don't have to mess with it.
			apiFetch.use( apiFetch.createNonceMiddleware( wpNonce ) );

			const result = await apiFetch({
				method: "POST",
				data: tabSettings,
				path: API_ENDPOINT,
			});

			if (result.success) {
				// Dynamically update settings Store for the current tab.
				updateSettings(tabSettings);

				// Move to the next tab.
				moveToNextTab();
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
