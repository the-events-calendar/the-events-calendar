import React from 'react';
import { _x } from '@wordpress/i18n';
import NewIcon from '../../components/Icons/New';
import {
	Button,
	__experimentalInputControl as InputControl,
} from '@wordpress/components';
import { useCallback, useRef, useState } from '@wordpress/element';
import { OrganizerData } from '../../../types/OrganizerData';

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
	const [ confirmEnabled, setConfirmEnabled ] = useState(
		currentValues.name !== ''
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
			<header className="classy-modal__header classy-modal__header--organizer">
				<NewIcon />
				<h4 className="classy-modal__header-title">
					{ isUpdate
						? _x(
								'Update Organizer',
								'Update organizer modal header title',
								'the-events-calendar'
						  )
						: _x(
								'New Organizer',
								'Inserti orgnanizer modal header title',
								'the-events-calendar'
						  ) }
				</h4>
			</header>

			<span className="classy-section-separator"></span>

			<section className="classy-modal__content classy-modal__content--organizer classy-field__inputs classy-field__inputs--unboxed">
				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x(
						'Name',
						'Name input label',
						'the-events-calendar'
					) }
					value={ currentValues.name }
					onChange={ ( value ) => {
						const newValue = value || '';
						setConfirmEnabled( newValue !== '' );

						return setValues( {
							...currentValues,
							name: newValue,
						} );
					} }
					required
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x(
						'Phone',
						'Phone input label',
						'the-events-calendar'
					) }
					value={ currentValues.phone }
					onChange={ ( value ) =>
						setValues( { ...currentValues, phone: value || '' } )
					}
					type="tel"
					placeholder=""
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x(
						'Website',
						'Website input label',
						'the-events-calendar'
					) }
					value={ currentValues.website }
					onChange={ ( value ) =>
						setValues( { ...currentValues, website: value || '' } )
					}
					type="url"
					placeholder=""
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x(
						'Email',
						'Email input label',
						'the-events-calendar'
					) }
					value={ currentValues.email }
					onChange={ ( value ) =>
						setValues( { ...currentValues, email: value || '' } )
					}
					type="email"
					placeholder=""
				/>
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
							? _x(
									'Update Organizer',
									'Update organizer button label',
									'the-events-calendar'
							  )
							: _x(
									'Create Organizer',
									'Create organizer button label',
									'the-events-calendar'
							  ) }
					</Button>
					<Button
						className="classy-button"
						onClick={ onCancel }
						variant="link"
					>
						{ _x(
							'Cancel',
							'Cancel button label',
							'the-events-calendar'
						) }
					</Button>
				</div>
			</footer>
		</div>
	);
}
