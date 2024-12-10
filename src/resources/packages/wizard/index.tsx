import React, { useState } from "react";
import domReady from "@wordpress/dom-ready";
import ReactDOM from "react-dom";
import { Modal } from "@wordpress/components";
import { useEffect } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import OnboardingTabs from "./components/tabs";
import { SETTINGS_STORE_KEY, MODAL_STORE_KEY } from "./data";

import "./index.css";

const OnboardingModal = ({ bootData }) => {
    const [isInitialized, setIsInitialized] = useState(false); // Track initialization state.

    // Dispatch actions
    const { initializeSettings } = useDispatch(SETTINGS_STORE_KEY);
    const { openModal } = useDispatch(MODAL_STORE_KEY);
    const { closeModal } = useDispatch(MODAL_STORE_KEY);

    // Initialize the settings store with boot data.
    useEffect(() => {
        initializeSettings(bootData);
        setIsInitialized(true); // Mark initialization as complete.
    }, []); // Empty dependency array ensures it runs only once.

	console.log(bootData);

    // Select state
    const finished = useSelect((select) => select(SETTINGS_STORE_KEY).getSetting("finished"));
    const begun = useSelect((select) => select(SETTINGS_STORE_KEY).getSetting("begun"));
    const isOpen = useSelect((select) => select(MODAL_STORE_KEY).getIsOpen());

    // Open modal conditionally after initialization. Prevents a second render.
    useEffect(() => {
        if (isInitialized && !finished) {
            openModal();
        }
    }, [begun, finished, isInitialized]);

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
    const trigger = document.getElementById("tec-events-onboarding-wizard");

    if (!trigger || isModalRendered) {
        return;
    }

    const containerId = trigger.dataset.containerElement;
    const bootData = trigger.dataset.wizardBootData;

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
});
