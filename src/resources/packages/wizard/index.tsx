import React from "react";
import domReady from '@wordpress/dom-ready';
import ReactDOM from 'react-dom';
import { Modal } from '@wordpress/components';
import { useSelect, useDispatch } from "@wordpress/data";
import OnboardingTabs from './components/Tabs';
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from './data';

import './index.css';

const OnboardingModal = ({ bootData }) => {
	const isOpen = useSelect((select) => select(MODAL_STORE_KEY).getModalState());
	const { closeModal } = useDispatch(MODAL_STORE_KEY);
	const { openModal } = useDispatch(MODAL_STORE_KEY);

	// Initialize the settings store.
	const {
		initializeSettings,
	} = useDispatch(SETTINGS_STORE_KEY);

	initializeSettings(bootData);

	return (
		<>
			{isOpen && (
				<Modal
					overlayClassName="tec-events-onboarding__modal-overlay"
					className="tec-events-onboarding__modal"
					contentLabel="TEC Onboarding Wizard"
					isDismissible={false}
					isFullScreen={true}
					initialTabName="intro"
					onRequestClose={closeModal}
					selectOnMove={false}
					shouldCloseOnClickOutside={false}
				>
					<OnboardingTabs />
				</Modal>
			)}
		</>
	);
};

domReady(() => {
	const button = document.getElementById('tec-events-onboarding-wizard');
	if ( ! button ) {
		return;
	}
	const containerId = button.dataset.containerElement;
	const bootData = button.dataset.wizardBootData;

	if (!containerId || !bootData) {
		console.warn("Container element or boot data is missing.");
		return;
	}

	const rootContainer = document.getElementById(containerId);
	if (!rootContainer) {
		console.warn(`Container with ID '${containerId}' not found.`);
		return;
	}

	const parsedBootData = JSON.parse(bootData);

	// Render the modal once in the container.
	ReactDOM.render(<OnboardingModal bootData={parsedBootData} />, rootContainer);

	// Add event listener to open the modal.
	button.addEventListener('click', (event) => {
		event.preventDefault();
		const { openModal } = wp.data.dispatch(MODAL_STORE_KEY); // Trigger the openModal action.
		openModal();
	});
});
