import domReady from '@wordpress/dom-ready';
import { createRoot } from 'react-dom/client';
import { useState } from '@wordpress/element';
import { Modal } from '@wordpress/components';
import OnboardingTabs from './components/tabs';
import * as TecIcon from './components/icons/tec';
import './index.css';

const OnboardingModal = () => {
	const [ isOpen, setOpen ] = useState( true );
	const openModal = () => setOpen( true );
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
    const root = createRoot(
        document.getElementById( 'tec-events-onboarding-wizard-target' )
    );

    root.render( <OnboardingModal /> );
} );
