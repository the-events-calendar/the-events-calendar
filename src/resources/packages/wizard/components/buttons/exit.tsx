import React from 'react';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { MODAL_STORE_KEY, SETTINGS_STORE_KEY } from '../../data';
import { API_ENDPOINT } from '../../data/settings/constants';

const ExitButton = () => {
	const closeModal = useDispatch( MODAL_STORE_KEY ).closeModal;

	const actionNonce = useSelect( ( select ) => select( SETTINGS_STORE_KEY ).getSetting( 'action_nonce' ), [] );
	const wpNonce = useSelect( ( select ) => select( SETTINGS_STORE_KEY ).getSetting( '_wpnonce' ), [] );

	const [ isClicked, setClicked ] = useState( false );

	useEffect( () => {
		const handleSkipWizard = async () => {
			// Add the wpnonce to the apiFetch middleware so we don't have to mess with it.
			apiFetch.use( apiFetch.createNonceMiddleware( wpNonce ) );

			const result = await apiFetch( {
				method: 'POST',
				data: {
					finished: true,
					begun: false,
					action_nonce: actionNonce,
				},
				path: API_ENDPOINT,
			} );

			setTimeout( () => {
				closeModal();
			}, 1000 );
		};

		if ( isClicked ) {
			handleSkipWizard();
		}
	}, [ isClicked ] );

	return (
		<Button
			variant="tertiary"
			onClick={ () => setClicked( true ) }
			className="tec-events-onboarding__button tec-events-onboarding__button--exit"
		>
			{ __( 'Skip guided setup', 'the-events-calendar' ) }
		</Button>
	);
};

export default ExitButton;
