import React from 'react';
import { BaseControl, ComboboxControl } from '@wordpress/components';
import { useState, useEffect, useMemo } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { SETTINGS_STORE_KEY } from '../../../data';
import NextButton from '../../buttons/next';
import SkipButton from '../../buttons/skip';
import GearIcon from './img/gear';

const dateFormatOptions = [
	{ label: _x( 'October 29, 2024', 'example date in "F j, Y" format', 'the-events-calendar' ), value: 'F j, Y' },
	{ label: _x( '29 October, 2024', 'example date in "j F, Y" format', 'the-events-calendar' ), value: 'j F, Y' },
	{ label: _x( '10/29/2024', 'example date in "m/d/Y" format', 'the-events-calendar' ), value: 'm/d/Y' },
	{ label: _x( '29/10/2024', 'example date in "d/m/Y" format', 'the-events-calendar' ), value: 'd/m/Y' },
	{ label: _x( '2024–10–29', 'example date in "Y-m-d" format', 'the-events-calendar' ), value: 'Y-m-d' },
];

const startDayOptions = [
	{ label: __( 'Sunday', 'the-events-calendar' ), value: '0' },
	{ label: __( 'Monday', 'the-events-calendar' ), value: '1' },
	{ label: __( 'Tuesday', 'the-events-calendar' ), value: '2' },
	{ label: __( 'Wednesday', 'the-events-calendar' ), value: '3' },
	{ label: __( 'Thursday', 'the-events-calendar' ), value: '4' },
	{ label: __( 'Friday', 'the-events-calendar' ), value: '5' },
	{ label: __( 'Saturday', 'the-events-calendar' ), value: '6' },
];

const SettingsContent = ({moveToNextTab, skipToNextTab}) => {
	const visitedFields = useSelect(select => select(SETTINGS_STORE_KEY).getVisitedFields(), []);
	const setVisitedField = useDispatch(SETTINGS_STORE_KEY).setVisitedField;
	const { currency, timezone_string, date_format, start_of_week, timezones, currencies }: { currency: string, timezone_string: string, date_format: string, start_of_week: number, timezones: Record<string, Record<string, string>>, currencies: Record<string, { symbol: string, name: string }> } = useSelect(
		(select) => {
			const store = select(SETTINGS_STORE_KEY);
			return {
				currency: store.getSetting('currency'),
				timezone_string: store.getSetting('timezone_string'),
				date_format: store.getSetting('date_format'),
				start_of_week: store.getSetting('start_of_week'),
				timezones: store.getSetting('timezones'),
				currencies: store.getSetting('currencies'),
			};
		},
		[]
	);
	const [ currencyCode, setCurrency ] = useState( currency );
	const [ timeZone, setTimeZone ] = useState( timezone_string );
	const [ dateFormat, setDateFormat ] = useState( date_format || dateFormatOptions[ 0 ].value );
	const [ weekStart, setWeekStart ] = useState( start_of_week || 0 );
	const [ canContinue, setCanContinue ] = useState( false );

	// Transform nested timezones object into flat array for ComboboxControl.
	const timezoneOptions = useMemo( () => {
		const options = [
			{ label: __( 'Select a non-UTC timezone.', 'the-events-calendar' ), value: '' }
		];

		Object.entries( timezones ).forEach( ( [ continent, cities ] ) => {
			Object.entries( cities as { [ key: string ]: string } ).forEach( ( [ key, city ] ) => {
				options.push( {
					label: `${continent}/${city}`,
					value: key
				} );
			} );
		} );

		return options;
	}, [ timezones ] );

	let timeZoneMessage = __( 'Please ensure your time zone is correct.', 'the-events-calendar' );

	if ( ! timezone_string ) {
		timeZoneMessage = __( 'Please select your time zone.', 'the-events-calendar' );
	} else if ( timezone_string.includes( 'UTC' ) ) {
		timeZoneMessage = __( 'Please select your time zone as UTC offsets are not supported.', 'the-events-calendar' );
	}

	// Create tabSettings object to pass to NextButton.
	const tabSettings = {
		currency: currencyCode,
		timezone_string: timeZone,
		date_format: dateFormat,
		start_of_week: weekStart,
		currentTab: 2, // Include the current tab index.
	};

	useEffect( () => {
		// Define the event listener function.
		const handleChange = ( event ) => {
			setVisitedField( event.target.id );
		};

		const fields = document.getElementById( 'settingsPanel' )?.querySelectorAll( 'input, select, textarea' );
		fields?.forEach( ( field ) => {
			field.addEventListener( 'change', handleChange );
		} );

		return () => {
			fields?.forEach( ( field ) => {
				field.removeEventListener( 'change', handleChange );
			} );
		};
	}, [] );

	// Compute whether the "Continue" button should be enabled
	useEffect( () => {
		// Since most of these are selects, we just ensure there is a value.
		const fieldsToCheck = {
			currencyCode,
			timeZone: isValidTimeZone(),
			dateFormat,
			weekStart,
			'visit-at-least-one': hasVisitedHere(),
		};

		setCanContinue( Object.values( fieldsToCheck ).every( ( field ) => !! field ) );
	}, [ currencyCode, timeZone, dateFormat, weekStart, visitedFields ] );

	const hasVisitedHere = () => {
		const values = [ !! currencyCode && !! timeZone && !! dateFormat && !! weekStart ];
		const fields = [ 'currencyCode', 'time-zone', 'dateFormat', 'weekStart' ];
		return fields.some( ( field ) => visitedFields.includes( field ) ) || values;
	};

	const isValidTimeZone = () => {
		const inputId = 'time-zone';
		const isVisited = visitedFields.includes( inputId );
		const isValid = ! isVisited || !! timeZone;

		return isValid;
	};

	const toggleClasses = ( field, fieldEle, parentEle, isValid ) => {
		if ( ! field ) {
			parentEle.classList.add( 'invalid', 'empty' );
			fieldEle.classList.add( 'invalid' );
		} else if ( ! isValid ) {
			parentEle.classList.add( 'invalid' );
			fieldEle.classList.add( 'invalid' );
		} else {
			parentEle.classList.remove( 'invalid', 'empty' );
			fieldEle.classList.remove( 'invalid' );
		}
	};

	return (
		<>
			<GearIcon />
			<div className="tec-events-onboarding__tab-header">
				<h1 className="tec-events-onboarding__tab-heading">
					{ __( 'Event Settings', 'the-events-calendar' ) }
				</h1>
				<p className="tec-events-onboarding__tab-subheader">
					{ __( "Let's get your events with the correct basic settings.", 'the-events-calendar' ) }
				</p>
			</div>
			<div className="tec-events-onboarding__tab-content">
				<div className="tec-events-onboarding__form-wrapper">
					<BaseControl
						__nextHasNoMarginBottom
						id="currency-code"
						label={ __( 'Currency symbol', 'the-events-calendar' ) }
						className="tec-events-onboarding__form-field"
					>
						<select onChange={ ( e ) => setCurrency( e.target.value ) } defaultValue={ currencyCode }>
							{ Object.entries( currencies ).map( ( [ key, data ] ) => (
								<option key={ key } value={ key }>
									{ data.symbol } ({ data.name })
								</option>
							) ) }
						</select>
						<span className="tec-events-onboarding__required-label">
							{ __( 'Currency symbol is required.', 'the-events-calendar' ) }
						</span>
						<span className="tec-events-onboarding__invalid-label">
							{ __( 'Currency symbol is invalid.', 'the-events-calendar' ) }
						</span>
					</BaseControl>

					<BaseControl
						__nextHasNoMarginBottom
						id="time-zone"
						label={ __( 'Time zone', 'the-events-calendar' ) }
						className="tec-events-onboarding__form-field"
					>
						<ComboboxControl
							id="time-zone"
							value={ timeZone }
							onChange={ ( value ) => {
								setTimeZone( value );
								setVisitedField( 'time-zone' );
							} }
							options={ timezoneOptions }
							placeholder={ __( 'Search for a timezone...', 'the-events-calendar' ) }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
						<span id="time-zone-description" className="tec-events-onboarding__field-description">
							{ timeZoneMessage }
						</span>
						<span className="tec-events-onboarding__required-label">
							{ __( 'A non-UTC time zone is required.', 'the-events-calendar' ) }
						</span>
						<span className="tec-events-onboarding__invalid-label">
							{ __( 'Time zone is invalid.', 'the-events-calendar' ) }
						</span>
					</BaseControl>

					<BaseControl
						__nextHasNoMarginBottom
						id="date-format"
						label={ __( 'Date format', 'the-events-calendar' ) }
						className="tec-events-onboarding__form-field"
					>
						<select
							id="date-format"
							onChange={ ( e ) => setDateFormat( e.target.value ) }
							defaultValue={ dateFormat }
						>
							{ dateFormatOptions.map( ( { label, value } ) => (
								<option key={ value } value={ value }>
									{ label }
								</option>
							) ) }
						</select>
						<span className="tec-events-onboarding__required-label">
							{ __( 'Date format is required.', 'the-events-calendar' ) }
						</span>
						<span className="tec-events-onboarding__invalid-label">
							{ __( 'Date format is invalid.', 'the-events-calendar' ) }
						</span>
					</BaseControl>

					<BaseControl
						__nextHasNoMarginBottom
						id="week-starts"
						label={ __( 'Your week starts on', 'the-events-calendar' ) }
						className="tec-events-onboarding__form-field"
					>
						<select
							id="week-starts"
							onChange={ ( e ) => setWeekStart( parseInt( e.target.value, 10 ) ) }
							defaultValue={ weekStart }
						>
							{ startDayOptions.map( ( { label, value } ) => (
								<option key={ value } value={ value }>
									{ label }
								</option>
							) ) }
						</select>
						<span className="tec-events-onboarding__required-label">
							{ __( 'Currency symbol is required.', 'the-events-calendar' ) }
						</span>
						<span className="tec-events-onboarding__invalid-label">
							{ __( 'Currency symbol is invalid.', 'the-events-calendar' ) }
						</span>
					</BaseControl>
				</div>
				<NextButton disabled={ ! canContinue } moveToNextTab={ moveToNextTab } tabSettings={ tabSettings } />
				<SkipButton skipToNextTab={ skipToNextTab } currentTab={ 2 } />
			</div>
		</>
	);
};

export default SettingsContent;
