import React from 'react';
import { StartOfWeek } from '../../../types/StartOfWeek';
import { DatePicker, Popover } from '@wordpress/components';
import { DatePickerEvent } from '@wordpress/components/build-types/date-time/types';
import { VirtualElement } from '@wordpress/components/build-types/popover/types';
import { SyntheticEvent } from '@wordpress/element';

function getDatePickerEventsBetweenDates(
	start: Date,
	end: Date
): DatePickerEvent[] {
	const dateArray: Date[] = [];
	let currentDate = new Date( start );
	while ( currentDate <= end ) {
		dateArray.push( new Date( currentDate ) );
		currentDate.setDate( currentDate.getDate() + 1 );
	}

	return dateArray.map( ( date: Date ): DatePickerEvent => {
		return { date };
	} );
}

export default function CalendarPopover( props: {
	anchor: Element | VirtualElement | null;
	startOfWeek: StartOfWeek;
	isSelectingDate: 'start' | 'end';
	isMultiday: boolean;
	date: Date;
	startDate: Date;
	endDate: Date;
	onClose: () => void;
	onChange: ( selecting: 'start' | 'end', date: string ) => void;
	onFocusOutside: ( event: SyntheticEvent ) => void;
} ) {
	const {
		anchor,
		startOfWeek,
		isSelectingDate,
		isMultiday,
		date,
		startDate,
		endDate,
		onClose,
		onChange,
		onFocusOutside,
	} = props;

	const events = getDatePickerEventsBetweenDates( startDate, endDate );

	// By default, all dates are valid.
	let isInvalidDate: ( date: Date ) => boolean = () => false;

	if ( isSelectingDate === 'end' ) {
		// The end date cannot be before the start date.
		isInvalidDate = ( date: Date ): boolean => {
			return startDate && date < startDate;
		};
	} else {
		// Selecting the start date.
		if ( isMultiday ) {
			// The start date cannot be after the end date in multiday mode.
			isInvalidDate = ( date: Date ): boolean => {
				return endDate && date > endDate;
			};
		}
	}

	return (
		<Popover
			anchor={ anchor }
			className="classy-component__popover classy-component__popover--calendar"
			expandOnMobile={ true }
			placement="bottom"
			noArrow={ false }
			offset={ 4 }
			onClose={ onClose }
			onFocusOutside={ onFocusOutside }
		>
			<DatePicker
				startOfWeek={ startOfWeek }
				currentDate={ date }
				onChange={ ( newDate: string ): void =>
					onChange( isSelectingDate as 'start' | 'end', newDate )
				}
				events={ events }
				isInvalidDate={ isInvalidDate }
			/>
		</Popover>
	);
}
