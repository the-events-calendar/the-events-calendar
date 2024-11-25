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
	const isSaving = useSelect(select => select(SETTINGS_STORE_KEY).getIsSaving() || false, []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const setSaving = useDispatch(SETTINGS_STORE_KEY).setSaving;
	const [isClicked, setClicked] = useState(false);

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
			onClick={setClicked}
			disabled={isSaving}
		>
			{__("Set up my calendar", "the-events-calendar")}
		</Button>
	);
};

export default SetupButton;
