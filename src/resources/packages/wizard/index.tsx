import React from "react";
import domReady from '@wordpress/dom-ready';
import ReactDOM from 'react-dom';
import { Modal } from '@wordpress/components';
import { useSelect, useDispatch } from "@wordpress/data";
import OnboardingTabs from './components/tabs';
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from './data';

import './index.css';

const OnboardingModal = ({ bootData }) => {
	// Initialize the settings store.
	const { initializeSettings } = useDispatch(SETTINGS_STORE_KEY);
	initializeSettings(bootData);

	const begun = useSelect((select) => select(SETTINGS_STORE_KEY).getSetting("begun"));
	const finished = useSelect((select) => select(SETTINGS_STORE_KEY).getSetting("finished"));
	const isOpen = !finished ? true : useSelect((select) => select(MODAL_STORE_KEY).getModalState());
	const { closeModal } = useDispatch(MODAL_STORE_KEY);
	const { openModal } = useDispatch(MODAL_STORE_KEY);

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
					shouldCloseOnEsc={false}
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
