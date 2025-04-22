import { format, getDate } from '@wordpress/date';
import { useRef, useState, useMemo } from '@wordpress/element';
import { ComboboxControl } from '@wordpress/components';
import { ComboboxControlOption } from '@wordpress/components/build-types/combobox-control/types';

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
	const [ , setSelectedTime ] = useState( format( 'H:i:s', currentDate ) );

	// Generate time options from `startDate` to `endDate`.
	const timeOptions = useMemo( (): ComboboxControlOption[] => {
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
			mStart =
				Math.ceil( start.getMinutes() / timeInterval ) * timeInterval;
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
	}, [ currentDate, timeFormat, timeInterval, startDate, endDate ] );

	const onChangeProxy = ( value: string ) => {
		try {
			const date = getDate(
				format( 'Y-m-d', currentDate ) + ' ' + value
			);

			if ( ! ( date instanceof Date ) ) {
				return;
			}

			onChange( date );
			setSelectedTime( value );
		} catch {
			return;
		}
	};

	return (
		<ComboboxControl
			className="classy-field__control classy-field__control--input classy-field__control--time-picker"
			allowReset={ false }
			value={ format( 'H:i:s', currentDate ) }
			options={ timeOptions }
			onChange={ onChangeProxy }
		/>
	);
}
