import React from "react";
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Button, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from "@wordpress/data";
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from "../../data";
import { API_ENDPOINT } from "../../data/settings/constants";
import { getVisitedFields } from "../../data/settings/selectors";

const NextButton = ({ disabled, moveToNextTab, tabSettings }) => {
	const completeTab = useDispatch(SETTINGS_STORE_KEY).completeTab;
	const { closeModal } = useDispatch(MODAL_STORE_KEY);

	const actionNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("action_nonce"), []);
	const wpNonce = useSelect(select => select(SETTINGS_STORE_KEY).getSetting("_wpnonce"), []);
	const updateSettings = useDispatch(SETTINGS_STORE_KEY).updateSettings;
	const getSettings = useSelect(select => select(SETTINGS_STORE_KEY).getSettings);
	const getCompletedTabs = useSelect(select => select(SETTINGS_STORE_KEY).getCompletedTabs);
	const getSkippedTabs = useSelect(select => select(SETTINGS_STORE_KEY).getSkippedTabs);
	const getVisitedFields = useSelect(SETTINGS_STORE_KEY).getVisitedFields;

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
			// Set the saving state.
			setSaving(true);

			if ( tabSettings.currentTab === 5 ) {
				// If we're on the last tab, we need to set the finished state to true.
				tabSettings.finished = true;
			}

			// Add our action nonce.
			tabSettings.action_nonce = actionNonce;

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
					completedTabs: getCompletedTabs(), // Include completedTabs
					skippedTabs: getSkippedTabs(),     // Include skippedTabs
					visitedFields: getVisitedFields(), // Include visitedFields
				},
				path: API_ENDPOINT,
			});

			if (result.success) {
				// Mark the step as completed on the landing page.
				const stepIndicators = Array.from(document.getElementsByClassName(`tec-events-onboarding-step-${tabSettings.currentTab}`));

				stepIndicators.map((stepIndicator: Element) => {
					stepIndicator.classList.add('tec-admin-page__onboarding-step--completed');
				});

				if ( tabSettings.currentTab === 5 ) {
					// If we're on step 5, we should remove the button to install Event Tickets.
					const installButton = document.querySelector('.tec-event-tickets-install-button');

					if ( installButton ) {
						installButton.style.display = 'none';
					}
				}

				// Reset the saving state.
				setSaving(false);

				// Move to the next tab.
				if ( tabSettings.currentTab === 5 ) {
					setTimeout(() => {
						closeModal();
					}, 1000);
				} else {
					moveToNextTab();
				}
			} else if ( tabSettings.currentTab === 5 ) {
				// If we're on the last tab and the install fails, reset the saving state and close the modal.
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
				className="tec-events-onboarding__button tec-events-onboarding__button--next"
			>
				{isSaving && __('Saving...', 'the-events-calendar')}{isSaving && <Spinner />}
				{!isSaving && __('Continue', 'the-events-calendar')}
			</Button>
		</>
	);
};

export default NextButton;
