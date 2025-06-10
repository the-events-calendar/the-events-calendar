import * as React from 'react';
import {useCallback, useState} from 'react';
import {_x} from '@wordpress/i18n';
import {__experimentalInputControl as InputControl, Button, CustomSelectControl} from '@wordpress/components';
import {CenteredSpinner, IconNew, InputLabel} from '@tec/common/classy/components';
import {VenueData} from '../../types/VenueData';
import {CustomSelectOption} from '@wordpress/components/build-types/custom-select-control/types';
import {useSelect} from "@wordpress/data";
import {SelectFunction} from "@wordpress/data/build-types/types";

const defaultValues = {
	name: '',
	address: '',
	city: '',
	country: '',
	zip: '',
	phone: '',
	website: '',
};

const countryPlaceholderOption: CustomSelectOption = {
	key: '0',
	name: _x( 'Select a country', 'Country selection placeholder option', 'the-events-calendar' ),
	value: '0',
};

export default function VenueUpsert( props: {
	isUpdate: boolean;
	onCancel: () => void;
	onSave: ( data: VenueData ) => void;
	values: VenueData;
} ) {
	const countryOptions:CustomSelectOption[] = useSelect(( select:SelectFunction)=>{
		// @ts-ignore
		return select('tec/classy').getCountryOptions();
	}, []);

	const { isUpdate, onCancel, onSave, values } = props;

	// States for venue details.
	const [ currentValues, setValues ] = useState( {
		...defaultValues,
		...values,
	} );

	// At a minimum, a Venue requires a name.
	const [ confirmEnabled, setConfirmEnabled ] = useState( currentValues.name !== '' );

	const [currentCountry, setCurrentCountry] = useState( countryPlaceholderOption );

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
			province: currentValues.province,
			state: currentValues.state,
			zip: currentValues.zip,
			phone: currentValues.phone,
			website: currentValues.website,
		};

		onSave( data );
	}, [ currentValues ] );

	const onCountryChange = useCallback(
		(newValue: {selectedItem: CustomSelectOption}): void => {},
		[]
	);

	if(countryOptions.length === 0){
		return  <CenteredSpinner/>
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
				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ <InputLabel label={ _x( 'Name', 'Name input label', 'the-events-calendar' ) } /> }
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
					label={ <InputLabel label={ _x( 'Address', 'Address input label', 'the-events-calendar' ) } /> }
					value={ currentValues.address }
					onChange={ ( value ) => setValues( { ...currentValues, address: value || '' } ) }
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ <InputLabel label={ _x( 'City ', 'City  input label', 'the-events-calendar' ) } /> }
					value={ currentValues.city }
					onChange={ ( value ) => setValues( { ...currentValues, city: value || '' } ) }
				/>

				<CustomSelectControl
					__next40pxDefaultSize
					className="classy-field__control classy-field__control--select"
					label={ _x( 'Country', 'country input label', 'the-events-calendar' ) }
					onChange={ onCountryChange }
					options={countryOptions}
					value={ currentCountry }
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={
						<InputLabel label={ _x( 'Postal Code', 'Postal code input label', 'the-events-calendar' ) } />
					}
					value={ currentValues.zip }
					onChange={ ( value ) => setValues( { ...currentValues, zip: value || '' } ) }
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ <InputLabel label={ _x( 'Phone', 'Phone input label', 'the-events-calendar' ) } /> }
					value={ currentValues.phone }
					onChange={ ( value ) => setValues( { ...currentValues, phone: value || '' } ) }
					type="tel"
				/>

				<InputControl
					className="classy-field__control classy-field__control--input"
					label={ <InputLabel label={ _x( 'Website', 'Website input label', 'the-events-calendar' ) } /> }
					value={ currentValues.website }
					onChange={ ( value ) => setValues( { ...currentValues, website: value || '' } ) }
					type="url"
				/>
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
