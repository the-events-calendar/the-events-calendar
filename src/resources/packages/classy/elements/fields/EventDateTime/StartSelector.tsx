import React, { Fragment, MouseEventHandler } from 'react';
import { StartOfWeek } from '../../../types/StartOfWeek';
import DatePicker from '../../components/DatePicker';
import TimePicker from '../../components/TimePicker';
import { RefObject, useRef } from '@wordpress/element';
import { localizedData } from '../../../localized-data';
import { format } from '@wordpress/date';
import { _x } from '@wordpress/i18n';

// @todo get this from the tec/classy store.
const timeInterval = localizedData?.settings?.timeInterval ?? 15;

export default function StartSelector( props: {
	dateWithYearFormat: string;
	endDate: Date;
	highightTime: boolean;
	isAllDay: boolean;
	isMultiday: boolean;
	isSelectingDate: 'start' | 'end' | false;
	onChange: ( selecting: 'start' | 'end', date: string ) => void;
	onClick: MouseEventHandler;
	onClose: () => void;
	onFocusOutside: () => void;
	startDate: Date;
	startOfWeek: StartOfWeek;
	timeFormat: string;
} ) {
	const {
		dateWithYearFormat,
		endDate,
		highightTime,
		isAllDay,
		isMultiday,
		isSelectingDate,
		onChange,
		onClick,
		onClose,
		onFocusOutside,
		startDate,
		startOfWeek,
		timeFormat,
	} = props;

	const ref: RefObject< HTMLDivElement > = useRef( null );

	const onTimeChange = ( date: Date ): void => {
		onChange( 'start', format( 'Y-m-d H:i:s', date ) );
	};

	return (
		<Fragment>
			<div
				className="classy-field__input classy-field__input--start-date classy-field__input--grow"
				ref={ ref }
			>
				<div className="classy-field__input-title">
					<h4>
						{ _x(
							'Date',
							'Event date selection input title',
							'the-events-calendar'
						) }
					</h4>
				</div>

				<DatePicker
					anchor={ ref.current }
					dateWithYearFormat={ dateWithYearFormat }
					endDate={ endDate }
					isSelectingDate={ isSelectingDate }
					isMultiday={ isMultiday }
					onClick={ onClick }
					onClose={ onClose }
					onChange={ onChange }
					onFocusOutside={ onFocusOutside }
					showPopover={ isSelectingDate === 'start' }
					startDate={ startDate }
					startOfWeek={ startOfWeek }
					currentDate={ startDate }
				/>
			</div>

			{ ! isAllDay && (
				<div className="classy-field__input classy-field__input--start-time">
					<div className="classy-field__input-title">
						<h4>
							{ _x(
								'Start Time',
								'Event start time selection input title',
								'the-events-calendar'
							) }
						</h4>
					</div>

					<TimePicker
						currentDate={ startDate }
						endDate={ isMultiday ? null : endDate }
						highlight={ highightTime }
						onChange={ onTimeChange }
						timeFormat={ timeFormat }
						timeInterval={ timeInterval }
					/>
				</div>
			) }
		</Fragment>
	);
}
