import React from "react";
import domReady from '@wordpress/dom-ready';
import ReactDOM from 'react-dom';
import { Modal } from '@wordpress/components';
import { useEffect } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import OnboardingTabs from './components/tabs';
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from './data';

import './index.css';

const OnboardingModal = ({ bootData }) => {
	// Initialize the settings store.
	const { initializeSettings } = useDispatch(SETTINGS_STORE_KEY);
	const { openModal } = useDispatch(MODAL_STORE_KEY); // Trigger the openModal action.
	const { closeModal } = useDispatch(MODAL_STORE_KEY);

	const finished = useSelect((select) => select(SETTINGS_STORE_KEY).getSetting("finished"));
	const begun = useSelect((select) => select(SETTINGS_STORE_KEY).getSetting("begun"));
	const isOpen = useSelect((select) => select(MODAL_STORE_KEY).getIsOpen());

	useEffect(() => {
		initializeSettings(bootData);
	}, []); // Empty dependency array ensures it runs only once

	useEffect(() => {
		if (!finished) {
			// Open the modal automatically if the onboarding has been started but not finished.
			openModal();
		}
	}, [begun, finished]);

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

let isModalRendered = false;

domReady(() => {
	const button = document.getElementById('tec-events-onboarding-wizard');
	if ( !button || isModalRendered ) {
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

	let parsedBootData;
	try {
		parsedBootData = JSON.parse(bootData);
	} catch (error) {
		console.error("Failed to parse bootData:", error);
		return;
	}

	// Render the modal once in the container.
	ReactDOM.render(<OnboardingModal bootData={parsedBootData} />, rootContainer);
	isModalRendered = true;

	// Add event listener to open the modal.
	button.addEventListener('click', (event) => {
		event.preventDefault();
		const { openModal } = wp.data.dispatch(MODAL_STORE_KEY); // Trigger the openModal action.
		openModal();
	});
});
