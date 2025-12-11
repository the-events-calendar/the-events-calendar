import * as React from 'react';
import { useCallback, useState } from 'react';
import { _x } from '@wordpress/i18n';
import { Button, __experimentalInputControl as InputControl } from '@wordpress/components';
import { isValidUrl } from '@tec/common/classy/functions';
import { OrganizerData } from '../../types/OrganizerData';
import { decodeEntities } from '@wordpress/html-entities';

const defaultValues = {
	name: '',
	phone: '',
	website: '',
	email: '',
};

export default function OrganizerUpsert( props: {
	isUpdate: boolean;
	onCancel: () => void;
	onSave: ( data: OrganizerData ) => void;
	values: OrganizerData;
} ) {
	const { isUpdate, onCancel, onSave, values } = props;

	// States for name, phone, website and email.
	const [ currentValues, setValues ] = useState( {
		...defaultValues,
		...values,
	} );

	// At a minimum an Organizers requires a name.
	const [ confirmEnabled, setConfirmEnabled ] = useState( currentValues.name !== '' );
	const [ hasValidUrl, setHasValidUrl ] = useState< boolean >( true );

	const onWebsiteChange = useCallback(
		( value: string | undefined ): void => {
			const websiteValue = value ?? '';

			if ( ! isValidUrl( websiteValue ) ) {
				setHasValidUrl( false );
				return;
			}

			setHasValidUrl( true );
			// Always update the input value to show what user typed
			setValues( { ...currentValues, website: websiteValue } );
		},
		[ currentValues ]
	);

	const invokeSaveWithData = useCallback( (): void => {
		if ( ! confirmEnabled ) {
			return;
		}

		const data: OrganizerData = {
			id: values.id,
			name: currentValues.name,
			phone: currentValues.phone,
			website: currentValues.website,
			email: currentValues.email,
		};

		onSave( data );
	}, [ currentValues ] );

	return (
		<div className="classy-root">
			<section className="classy-modal__content classy-modal__content--organizer classy-field__inputs classy-field__inputs--unboxed">
				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x( 'Name', 'Name input label', 'the-events-calendar' ) }
					value={ decodeEntities( currentValues.name ) }
					onChange={ ( value ) => {
						const newValue = value || '';
						setConfirmEnabled( newValue.trim() !== '' );

						return setValues( {
							...currentValues,
							name: newValue,
						} );
					} }
					required
					__next40pxDefaultSize
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x( 'Phone', 'Phone input label', 'the-events-calendar' ) }
					value={ decodeEntities( currentValues.phone ) }
					onChange={ ( value ) => setValues( { ...currentValues, phone: value || '' } ) }
					type="tel"
					placeholder=""
					__next40pxDefaultSize
				/>

				<InputControl
					className={ `classy-field__control classy-field__control--input${
						! hasValidUrl ? ' classy-field__control--invalid' : ''
					}` }
					label={ _x( 'Website', 'Website input label', 'the-events-calendar' ) }
					value={ decodeEntities( currentValues.website ) }
					onChange={ onWebsiteChange }
					type="url"
					placeholder=""
					__next40pxDefaultSize
				/>
				{ ! hasValidUrl && (
					<div className="classy-field__input-note classy-field__input-note--error">
						{ _x( 'Must be a valid URL', 'Website input error message', 'the-events-calendar' ) }
					</div>
				) }

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x( 'Email', 'Email input label', 'the-events-calendar' ) }
					value={ decodeEntities( currentValues.email ) }
					onChange={ ( value ) => setValues( { ...currentValues, email: value || '' } ) }
					type="email"
					placeholder=""
					__next40pxDefaultSize
				/>
				<div className="classy-field__input-note">
					{ _x(
						'The email address will be obfuscated on this site to avoid being harvested by spammers.',
						'Email input obfuscation note',
						'the-events-calendar'
					) }
				</div>
			</section>

			<footer className="classy-modal__footer classy-modal__footer--organizer">
				<div className="classy-modal__actions classy-modal__actions--organizer">
					<Button
						aria-disabled={ ! confirmEnabled }
						className="classy-button"
						onClick={ invokeSaveWithData }
						variant="primary"
					>
						{ values.id
							? _x( 'Update Organizer', 'Update organizer button label', 'the-events-calendar' )
							: _x( 'Create Organizer', 'Create organizer button label', 'the-events-calendar' ) }
					</Button>
					<Button className="classy-button" onClick={ onCancel } variant="link">
						{ _x( 'Cancel', 'Cancel button label', 'the-events-calendar' ) }
					</Button>
				</div>
			</footer>
		</div>
	);
}
