import React from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { SETTINGS_STORE_KEY } from '../../data';

/**
 * Sends the user to the Liquid Web portal to activate a license.
 *
 * The URL is built server side and carries a return address, so the portal
 * brings the user back to the guided setup once they are done.
 *
 * Renders nothing when no URL was supplied, which is the case on installs
 * whose bundled Harbor library predates the activation URL API.
 */
const ActivateButton = () => {
	const activationUrl = useSelect( ( select ) => select( SETTINGS_STORE_KEY ).getSetting( 'activationUrl' ), [] );

	if ( ! activationUrl ) {
		return null;
	}

	return (
		<Button
			variant="secondary"
			href={ activationUrl }
			className="tec-events-onboarding__button tec-events-onboarding__button--activate"
		>
			{ __( 'Activate license', 'the-events-calendar' ) }
		</Button>
	);
};

export default ActivateButton;
