import domReady from '@wordpress/dom-ready';
import { createRoot } from 'react-dom/client';
import { useState } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';
import OnboardingTabs from './components/tabs';
import * as TecIcon from './components/icons/tec';
import './index.css';

const OnboardingModal = () => {
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
				icon={TecIcon.default()}
				isDismissible={false}
				isFullScreen={true}
				initialTabName="intro"
				onRequestClose={ closeModal }
				selectOnMove={false}
				shouldCloseOnClickOutside={false}
			>
				<OnboardingTabs closeModal={closeModal} />
			</Modal>
		) }
		</>
	);
};

domReady( () => {
	const initializeWizard = (data) => {
		const {containerElement} = data;
		const root = createRoot(
			containerElement
		);

		root.render( <OnboardingModal /> );
	};

	document.querySelectorAll( '.tec-events-onboarding-wizard' ).forEach( ( element ) => {
		element.addEventListener( 'click', (event) => {
			event.preventDefault();
			initializeWizard({
				containerElement: document.getElementById( element.dataset.containerElement )
			});
		});
	});
} );
