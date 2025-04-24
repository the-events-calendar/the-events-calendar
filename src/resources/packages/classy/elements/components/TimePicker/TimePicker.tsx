import React, {
	FocusEventHandler,
	FormEventHandler,
	HtmlHTMLAttributes,
	KeyboardEventHandler,
} from 'react';
import { format, getDate } from '@wordpress/date';
import {
	useRef,
	useState,
	useMemo,
	useCallback,
	Fragment,
} from '@wordpress/element';
import {
	ComboboxControl,
	__experimentalInputControl as InputControl,
} from '@wordpress/components';
import { ComboboxControlOption } from '@wordpress/components/build-types/combobox-control/types';
import { getValidDateOrNull } from '../../../functions/dateUtils';
import { _x } from '@wordpress/i18n';

function getTimeOptions(
	currentDate: Date,
	startDate: Date | null = null,
	endDate: Date | null = null,
	timeInterval: number,
	timeFormat: string
): ComboboxControlOption[] {
	const times: ComboboxControlOption[] = [];

	// Set default start and end dates if null.
	let start: Date;
	if ( startDate ) {
		start = new Date( startDate );
	} else {
		start = new Date();
		start.setHours( 0, 0, 0 );
	}

	let end: Date;
	if ( endDate ) {
		end = new Date( endDate );
	} else {
		end = new Date();
		end.setHours( 23, 59, 0 );
	}

	// Adjust start time to the nearest interval.
	let hStart = start.getHours();
	let mStart = 0;
	if ( startDate ) {
		mStart = Math.ceil( start.getMinutes() / timeInterval ) * timeInterval;
		if ( mStart === 60 ) {
			mStart = 0;
			hStart += 1;
		}
	}

	// Loop through hours and minutes.
	for ( let h = hStart; h < 24; h++ ) {
		let m = h === hStart ? mStart : 0;
		while ( m < 60 ) {
			const date = new Date( currentDate );
			date.setHours( h, m, 0, 0 );

			// Check if the generated time is within the range
			if ( ! startDate || date >= start ) {
				if ( ! endDate || date <= end ) {
					times.push( {
						label: format( timeFormat, date ),
						value: format( 'H:i:s', date ),
					} );
				}
			}

			m += timeInterval;
		}
	}
	return times;
}

function isDateBetweenLimits(
	date: Date,
	startDate: Date | null,
	endDate: Date | null
): boolean {
	if ( startDate === null && endDate === null ) {
		return true;
	}

	const isAfterStart = startDate === null || date >= startDate;
	const isBeforeEnd = endDate === null || date <= endDate;

	return isAfterStart && isBeforeEnd;
}

export default function TimePicker( props: {
	currentDate: Date;
	startDate?: Date;
	endDate?: Date;
	timeFormat: string;
	timeInterval: number; // In minutes.
	onChange: ( date: Date ) => void;
} ) {
	const {
		currentDate,
		startDate = null,
		endDate = null,
		timeFormat,
		timeInterval,
		onChange,
	} = props;

	const [ selectedTime, setSelectedTime ] = useState(
		format( 'H:i:s', currentDate )
	);
	const isValidDate =
		currentDate instanceof Date &&
		isDateBetweenLimits( currentDate, startDate, endDate );
	const [ isValidInput, setIsValidInput ] = useState( isValidDate );
	const currenDateYearMonthDayPrefix = format( 'Y-m-d ', currentDate );

	// Calculate all the available time options.
	const timeOptions = useMemo( (): ComboboxControlOption[] => {
		return getTimeOptions(
			currentDate,
			startDate,
			endDate,
			timeInterval,
			timeFormat
		);
	}, [ currentDate, timeFormat, timeInterval, startDate, endDate ] );

	// Set the initial options to all available time options.
	const [ options, setOptions ] = useState( timeOptions );

	const onChangeProxy = ( value: string | null | undefined ): void => {
		if ( ! value ) {
			return;
		}

		const date = getValidDateOrNull( currenDateYearMonthDayPrefix + value );

		if ( date === null ) {
			setIsValidInput( false );
			return;
		}

		setIsValidInput( isDateBetweenLimits( date, startDate, endDate ) );
		setSelectedTime( value );
		onChange( date );
	};

	const onFilterValueChange = ( value: string | null | undefined ): void => {
		if ( ! value ) {
			return;
		}

		// Reduce the options to only those whose label start with the value.
		const filteredOptions = timeOptions.filter( ( option ) =>
			option.label.startsWith( value )
		);

		if ( filteredOptions.length > 0 ) {
			// Render the remaining options.
			setOptions( filteredOptions );
		} else {
			// Render with only one option that indicates the user is inserting a custom time.
			setOptions( [
				{
					label: value,
					value: value,
					isCustom: true,
				},
			] );
			setSelectedTime( value );
		}

		// Set whether the date is valid.
		const date = getValidDateOrNull( currenDateYearMonthDayPrefix + value );
		setIsValidInput(
			date !== null && isDateBetweenLimits( date, startDate, endDate )
		);
	};

	console.log(
		`TimePicker rerendering for currentDate=${ currentDate } startDate=${ startDate } endDate=${ endDate } isValidInput=${ isValidInput } isValidDate=${ isValidDate }`
	);

	let className =
		'classy-field__control classy-field__control--input classy-field__control--time-picker';
	if ( ! ( isValidInput && isValidDate ) ) {
		className += ' classy-field__control--invalid';
	}

	return (
		<ComboboxControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			className={ className }
			allowReset={ false }
			value={ selectedTime }
			options={ options }
			onChange={ onChangeProxy }
			onFilterValueChange={ onFilterValueChange }
			expandOnFocus={
				! ( options.length === 1 && options[ 0 ].isCustom )
			}
		/>
	);
}
