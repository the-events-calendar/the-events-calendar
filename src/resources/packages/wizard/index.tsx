import React from "react";
import domReady from '@wordpress/dom-ready';
import { createRoot } from 'react-dom/client';
import { useState } from '@wordpress/element';
import { Modal } from '@wordpress/components';
import { useSelect, useDispatch } from "@wordpress/data";
import OnboardingTabs from './components/Tabs';
import { SETTINGS_STORE_KEY } from './data';
import './index.css';

const OnboardingModal = ({ bootData }) => {
	const [isOpen, setOpen] = useState(true);
	const closeModal = () => setOpen(false);

	const settings = useSelect(select => {
		return select(SETTINGS_STORE_KEY).getProducts();
	  }, []);
	  const { initializeSettings, createSetting, updateSetting, deleteSetting } = useDispatch(
		SETTINGS_STORE_KEY
	  );

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
					<OnboardingTabs bootData={bootData} closeModal={closeModal} />
				</Modal>
			)}
		</>
	);
};

domReady(() => {
	// Store our created roots in a map so we can reuse them.
	const roots = new Map<string, ReturnType<typeof createRoot>>();

	document.querySelectorAll('.tec-events-onboarding-wizard').forEach((element) => {
		const containerId = element.dataset.containerElement;
		const bootData = element.dataset.wizardBootData;

		if (!containerId || !bootData) {
			console.warn("Container element or boot data is missing.");
			return;
		}

		const rootContainer = document.getElementById(containerId);
		if (!rootContainer) {
			console.warn(`Container with ID '${containerId}' not found.`);
			return;
		}

		// Check if the root for this container already exists.
		let root = roots.get(containerId);
		if (!root) {
			// Create and store the root only if it doesn’t already exist.
			root = createRoot(rootContainer);
			roots.set(containerId, root);
		}

		const parsedBootData = JSON.parse(bootData);

		element.addEventListener('click', (event) => {
			event.preventDefault();
			root!.render(<OnboardingModal bootData={parsedBootData} />);
		});
	});
});
