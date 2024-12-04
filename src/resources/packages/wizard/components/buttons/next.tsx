import React from "react";
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Button, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from "../../data";
import { API_ENDPOINT } from "../../data/settings/constants";

const NextButton = ({ disabled, moveToNextTab, tabSettings }) => {
	const actionNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("action_nonce"), []);
	const wpNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("_wpnonce"), []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const { closeModal } = useDispatch(MODAL_STORE_KEY);
	const [isSaving, setSaving] = useState(false);
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
				// If we saved a venue or organizer, we need to update the ID in the settings to prevent trying to save again.
				if ( result.venue_id ) {
					tabSettings.venue.id = result.venue_id;
				}
				if ( result.organizer_id ) {
					tabSettings.organizer.id = result.organizer_id;
				}

				// Dynamically update settings Store for the current tab.
				updateSettings(tabSettings);

				// Move to the next tab.
				if ( tabSettings.currentTab === 5 ) {
					setSaving(false);
					setTimeout(() => {
						closeModal();
					}, 1000);
				} else {
					setSaving(false);

					moveToNextTab();
				}
			} else if ( tabSettings.currentTab === 5 ) {
				// If we're on the last tab and the install fails, close the modal.
				setSaving(false);
				setTimeout(() => {
					closeModal();
				}, 1000);
			}

			setSaving(false);
		};

		if (isClicked) {
			handleTabChange();
		}
	}, [isClicked]);

	return (
		<>
			<Button
				variant="primary"
				disabled={disabled || isSaving}
				onClick={() => setClicked(true)}
			>
				{isSaving && __('Saving...', 'the-events-calendar')}{isSaving && <Spinner />}
				{!isSaving && __('Continue', 'the-events-calendar')}
			</Button>
		</>
	);
};

export default NextButton;
