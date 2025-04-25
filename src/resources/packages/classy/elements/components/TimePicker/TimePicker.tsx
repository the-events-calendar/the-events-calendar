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
	RefObject,
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

	const currenDateIsValid = isDateBetweenLimits(
		currentDate,
		startDate,
		endDate
	);
	const currenDateYearMonthDayPrefix = format( 'Y-m-d ', currentDate );

	// Store a reference to start and end date to know if they changed.
	// When either changes, then it's due to a user interaction with another control.
	const dateRefs = useRef( { startDate, endDate } );

	const [ selectedTime, setSelectedTime ] = useState(
		format( 'H:i:s', currentDate )
	);

	// This is the internal to the component valid state: initially set to the current date
	// validity, but then controlled by the user input on this control.
	const [ isValid, setIsValid ] = useState( currenDateIsValid );

	let isControlValueValid = isValid;

	// If the current date does not match either start or end date, then it changed across
	// re-renders due to the user interaction with another control: the validity of this
	// control is the new current date validity.
	const datesChanged =
		currentDate != dateRefs.current.startDate &&
		currentDate != dateRefs.current.endDate;

	if ( datesChanged ) {
		isControlValueValid = currenDateIsValid;

		// Store the new start and end dates.
		dateRefs.current = { startDate, endDate };
	}

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
	const [ options, setOptions ] = useState( () => {
		const formattedCurrentDate = format( 'H:i:s', currentDate );
		const filteredtOptions = timeOptions.filter( ( option ) => {
			return option.value === formattedCurrentDate;
		} );

		if ( filteredtOptions.length > 0 ) {
			// The initially selected date is among the options, provide all the options.
			return timeOptions;
		}

		// The initially selected date is not among the options, provide only the custom option.
		return [
			{
				label: format( timeFormat, currentDate ),
				value: format( 'H:i:s', currentDate ),
				isCustom: true,
			},
		];
	} );

	const onChangeProxy = useCallback(
		( value: string | null | undefined ): void => {
			if ( ! value ) {
				return;
			}

			const date = getValidDateOrNull(
				currenDateYearMonthDayPrefix + value
			);

			if ( date === null ) {
				setIsValid( false );
				return;
			}

			setIsValid( isDateBetweenLimits( date, startDate, endDate ) );
			setSelectedTime( value );
			onChange( date );
		},
		[ currenDateYearMonthDayPrefix, startDate, endDate, onChange ]
	);

	const onFilterValueChange = useCallback(
		( value: string | null | undefined ): void => {
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
			const date = getValidDateOrNull(
				currenDateYearMonthDayPrefix + value
			);
			setIsValid(
				date !== null && isDateBetweenLimits( date, startDate, endDate )
			);
		},
		[ timeOptions, currenDateYearMonthDayPrefix, startDate, endDate ]
	);

	let className =
		'classy-field__control classy-field__control--input classy-field__control--time-picker';

	if ( ! isControlValueValid ) {
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
