import React from 'react';
import { _x } from '@wordpress/i18n';
import NewIcon from '../../components/Icons/New';
import {
	Button,
	__experimentalInputControl as InputControl,
} from '@wordpress/components';
import { useRef } from '@wordpress/element';

const defaultValues = {
	name: '',
	phone: '',
	website: '',
	email: '',
};

export default function OrganizerUpsert( props: {
	onCancel: () => void;
	onSave: () => void;
} ) {
	const { onCancel, onSave } = props;

	// States for name, phone, website and email.
	const [ values, setValues ] = React.useState( defaultValues );

	const component = (
		<div className="classy-root">
			<header className="classy-modal__header classy-modal__header--organizer">
				<NewIcon />
				<h4 className="classy-modal__header-title">
					{ _x(
						'New Organizer',
						'Upsert modal header title',
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
					value={ values.name }
					onChange={ ( value ) =>
						setValues( { ...values, name: value || '' } )
					}
					required
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ _x(
						'Phone',
						'Phone input label',
						'the-events-calendar'
					) }
					value={ values.phone }
					onChange={ ( value ) =>
						setValues( { ...values, phone: value || '' } )
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
					value={ values.website }
					onChange={ ( value ) =>
						setValues( { ...values, website: value || '' } )
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
					value={ values.email }
					onChange={ ( value ) =>
						setValues( { ...values, email: value || '' } )
					}
					type="email"
					placeholder=""
				/>
			</section>

			<footer className="classy-modal__footer classy-modal__footer--organizer">
				<div className="classy-modal__actions classy-modal__actions--organizer">
					<Button className="classy-button" variant="primary">
						{ _x(
							'Create Organizer',
							'Create organizer button label',
							'the-events-calendar'
						) }
					</Button>
					<Button className="classy-button" variant="link">
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

	return component;
}
