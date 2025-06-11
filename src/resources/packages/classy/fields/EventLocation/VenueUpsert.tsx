import * as React from 'react';
import { useCallback, useState } from 'react';
import { _x } from '@wordpress/i18n';
import { __experimentalInputControl as InputControl, Button, CustomSelectControl } from '@wordpress/components';
import { CenteredSpinner, IconNew, LabeledInput } from '@tec/common/classy/components';
import { VenueData } from '../../types/VenueData';
import { CustomSelectOption } from '@wordpress/components/build-types/custom-select-control/types';
import { useSelect } from '@wordpress/data';
import { SelectFunction } from '@wordpress/data/build-types/types';

const defaultValues = {
	name: '',
	address: '',
	city: '',
	country: '',
	countryCode: '',
	zip: '',
	phone: '',
	website: '',
};

const countryPlaceholderOption: CustomSelectOption = {
	key: '0',
	name: _x( 'Select a country', 'Country selection placeholder option', 'the-events-calendar' ),
	value: '0',
};

const usStatePlaceholderOption: CustomSelectOption = {
	key: '0',
	name: _x( 'Select a state', 'US state selection placeholder option', 'the-events-calendar' ),
	value: '0',
};

export default function VenueUpsert( props: {
	isUpdate: boolean;
	onCancel: () => void;
	onSave: ( data: VenueData ) => void;
	values: VenueData;
} ) {
	const {
		countryOptions,
		usStatesOptions,
	}: {
		countryOptions: CustomSelectOption[];
		usStatesOptions: CustomSelectOption[];
	} = useSelect( ( select: SelectFunction ) => {
		const selector: {
			getCountryOptions: () => CustomSelectOption[];
			getUsStatesOptions: () => CustomSelectOption[];
		} = select( 'tec/classy' );

		const countryOptions = selector.getCountryOptions();
		const usStatesOptions = selector.getUsStatesOptions();

		return {
			countryOptions,
			usStatesOptions,
		};
	}, [] );

	const { isUpdate, onCancel, onSave, values } = props;

	// States for venue details.
	const [ currentValues, setCurrentValues ] = useState( {
		...defaultValues,
		...values,
	} );

	// At a minimum, a Venue requires a name.
	const [ confirmEnabled, setConfirmEnabled ] = useState( currentValues.name !== '' );

	const [ countryOption, setCountryOption ] = useState( countryPlaceholderOption );
	const [ usStateOption, setUsStateOption ] = useState( usStatePlaceholderOption );

	const [ isUnitedStates, setIsUnitedStates ] = useState( values.countryCode === 'US' );

	const invokeSaveWithData = useCallback( (): void => {
		if ( ! confirmEnabled ) {
			return;
		}

		const data: VenueData = {
			id: values.id,
			name: currentValues.name,
			address: currentValues.address,
			city: currentValues.city,
			country: currentValues.country,
			countryCode: currentValues.countryCode,
			province: currentValues.province,
			stateprovince: currentValues.stateprovince,
			zip: currentValues.zip,
			phone: currentValues.phone,
			website: currentValues.website,
		};

		onSave( data );
	}, [ currentValues ] );

	const onCountryChange = useCallback(
		( newValue: { selectedItem: CustomSelectOption } ): void => {
			setIsUnitedStates( newValue.selectedItem.key === 'US' );
			setCurrentValues( {
				...currentValues,
				country: newValue.selectedItem.name,
				countryCode: newValue.selectedItem.key,
			} );
			setCountryOption( newValue.selectedItem );
		},
		[ currentValues ]
	);

	const onUsStateChange = useCallback(
		( newValue: { selectedItem: CustomSelectOption } ) => {
			setCurrentValues( { ...currentValues, stateprovince: newValue.selectedItem.name } );
			setUsStateOption( newValue.selectedItem );
		},
		[ currentValues ]
	);

	if ( countryOptions.length === 0 || usStatesOptions.length === 0 ) {
		return <CenteredSpinner />;
	}

	return (
		<div className="classy-root">
			<header className="classy-modal__header classy-modal__header--venue">
				<IconNew />
				<h4 className="classy-modal__header-title">
					{ isUpdate
						? _x( 'Update Venue', 'Update venue modal header title', 'the-events-calendar' )
						: _x( 'New Venue', 'Insert venue modal header title', 'the-events-calendar' ) }
				</h4>
			</header>

			<span className="classy-section-separator"></span>

			<section className="classy-modal__content classy-modal__content--venue classy-field__inputs classy-field__inputs--unboxed">
				<LabeledInput label={ _x( 'Name', 'Name input label', 'the-events-calendar' ) }>
					<InputControl
						className="classy-field__control classy-field__control--input"
						label={ _x( 'Name', 'Name input label', 'the-events-calendar' ) }
						hideLabelFromVision={ true }
						value={ currentValues.name }
						onChange={ ( value ) => {
							const newValue = value || '';
							setConfirmEnabled( newValue !== '' );

							return setCurrentValues( {
								...currentValues,
								name: newValue,
							} );
						} }
						required
					/>
				</LabeledInput>

				<LabeledInput label={ _x( 'Address', 'Address input label', 'the-events-calendar' ) }>
					<InputControl
						className="classy-field__control classy-field__control--input"
						label={ _x( 'Address', 'Address input label', 'the-events-calendar' ) }
						hideLabelFromVision={ true }
						value={ currentValues.address }
						onChange={ ( value ) => setCurrentValues( { ...currentValues, address: value || '' } ) }
					/>
				</LabeledInput>

				<LabeledInput label={ _x( 'City ', 'City  input label', 'the-events-calendar' ) }>
					<InputControl
						className="classy-field__control classy-field__control--input"
						label={ _x( 'City ', 'City  input label', 'the-events-calendar' ) }
						hideLabelFromVision={ true }
						value={ currentValues.city }
						onChange={ ( value ) => setCurrentValues( { ...currentValues, city: value || '' } ) }
					/>
				</LabeledInput>

				<LabeledInput label={ _x( 'Country', 'country input label', 'the-events-calendar' ) }>
					<CustomSelectControl
						__next40pxDefaultSize
						hideLabelFromVision={ true }
						className="classy-field__control classy-field__control--select"
						label={ _x( 'Country', 'country input label', 'the-events-calendar' ) }
						onChange={ onCountryChange }
						options={ countryOptions }
						value={ countryOption }
					/>
				</LabeledInput>

				{ isUnitedStates ? (
					<LabeledInput label={ _x( 'State', 'United Stateslic state input label', 'the-events-calendar' ) }>
						<CustomSelectControl
							className="classy-field__control classy-field__control--input"
							label={ _x( 'State', 'United State state input label', 'the-events-calendar' ) }
							hideLabelFromVision={ true }
							value={ usStateOption }
							onChange={ onUsStateChange }
							options={ usStatesOptions }
						/>
					</LabeledInput>
				) : (
					<LabeledInput
						label={ _x(
							'State or Province',
							'State input label; used if the country is not the US',
							'the-events-calendar'
						) }
					>
						<InputControl
							className="classy-field__control classy-field__control--input"
							label={ _x(
								'State or Province',
								'State input label; used if the country is not the US',
								'the-events-calendar'
							) }
							hideLabelFromVision={ true }
							value={ currentValues.stateprovince }
							onChange={ ( newValue ) => {
								setCurrentValues( { ...currentValues, stateprovince: newValue } );
							} }
						/>
					</LabeledInput>
				) }

				<LabeledInput label={ _x( 'Postal Code', 'Postal code input label', 'the-events-calendar' ) }>
					<InputControl
						className="classy-field__control classy-field__control--input"
						label={ _x( 'Postal Code', 'Postal code input label', 'the-events-calendar' ) }
						hideLabelFromVision={ true }
						value={ currentValues.zip }
						onChange={ ( value ) => setCurrentValues( { ...currentValues, zip: value || '' } ) }
					/>
				</LabeledInput>

				<LabeledInput label={ _x( 'Phone', 'Phone input label', 'the-events-calendar' ) }>
					<InputControl
						className="classy-field__control classy-field__control--input"
						label={ _x( 'Phone', 'Phone input label', 'the-events-calendar' ) }
						hideLabelFromVision={ true }
						value={ currentValues.phone }
						onChange={ ( value ) => setCurrentValues( { ...currentValues, phone: value || '' } ) }
						type="tel"
					/>
				</LabeledInput>

				<LabeledInput label={ _x( 'Website', 'Website input label', 'the-events-calendar' ) }>
					<InputControl
						className="classy-field__control classy-field__control--input"
						label={ _x( 'Website', 'Website input label', 'the-events-calendar' ) }
						hideLabelFromVision={ true }
						value={ currentValues.website }
						onChange={ ( value ) => setCurrentValues( { ...currentValues, website: value || '' } ) }
						type="url"
					/>
				</LabeledInput>
			</section>

			<footer className="classy-modal__footer classy-modal__footer--venue">
				<div className="classy-modal__actions classy-modal__actions--venue">
					<Button
						aria-disabled={ ! confirmEnabled }
						className="classy-button"
						onClick={ invokeSaveWithData }
						variant="primary"
					>
						{ values.id
							? _x( 'Update Venue', 'Update venue button label', 'the-events-calendar' )
							: _x( 'Create Venue', 'Create venue button label', 'the-events-calendar' ) }
					</Button>
					<Button className="classy-button" onClick={ onCancel } variant="link">
						{ _x( 'Cancel', 'Cancel button label', 'the-events-calendar' ) }
					</Button>
				</div>
			</footer>
		</div>
	);
}
