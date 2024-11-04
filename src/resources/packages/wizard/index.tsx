import React from "react";
import domReady from '@wordpress/dom-ready';
import { createRoot } from 'react-dom/client';
import { useState } from '@wordpress/element';
import { Modal } from '@wordpress/components';
import OnboardingTabs from './components/tabs';
import './index.css';

const OnboardingModal = ({fieldValues}) => {
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
				<OnboardingTabs fieldValues={fieldValues} closeModal={closeModal} />
			</Modal>
		) }
		</>
	);
};

domReady( () => {
	const initializeWizard = (containerElement, fieldValues) => {
		const root = createRoot(
			containerElement
		);

		root.render( <OnboardingModal fieldValues={fieldValues} /> );
	};

	document.querySelectorAll( '.tec-events-onboarding-wizard' ).forEach( ( element ) => {
		element.addEventListener( 'click', (event) => {
			event.preventDefault();
			initializeWizard({
				containerElement: document.getElementById( (element as HTMLElement).dataset.containerElement || '' ),
				fieldValues: (element as HTMLElement).dataset.fieldValues,
			});
		});
	});
} );
