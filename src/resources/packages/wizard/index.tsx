import React from "react";
import domReady from '@wordpress/dom-ready';
import { createReduxStore, register } from '@wordpress/data';
import { createRoot } from 'react-dom/client';
import { useState } from '@wordpress/element';
import { Modal } from '@wordpress/components';
import OnboardingTabs from './components/Tabs';
import './index.css';

const OnboardingModal = ({bootData}) => {
	const [ isOpen, setOpen ] = useState( true );
	const openModal = () => setOpen( false );
	const closeModal = () => setOpen( false );

	return (
		<>
		{ isOpen && (
			<Modal
				overlayClassName="tec-events-onboarding__modal-overlay"
				className="tec-events-onboarding__modal"
				contentLabel="TEC Onboarding Wizard"
				isDismissible={false}
				isFullScreen={true}
				initialTabName="intro"
				onRequestClose={ closeModal }
				selectOnMove={false}
				shouldCloseOnClickOutside={false}
			>
				<OnboardingTabs bootData={JSON.parse(bootData)} closeModal={closeModal} />
			</Modal>
		) }
		</>
	);
};

domReady( () => {
	const initializeWizard = (containerElement, bootData) => {
		const root = createRoot( containerElement );

		root.render( <OnboardingModal bootData={bootData} /> );
	};

	document.querySelectorAll( '.tec-events-onboarding-wizard' ).forEach( ( element ) => {
		element.addEventListener( 'click', (event) => {
			event.preventDefault();
			initializeWizard(
				document.getElementById( (element as HTMLElement).dataset.containerElement || '' ),
				(element as HTMLElement).dataset.wizardBootData
			);
		});
	});
} );
