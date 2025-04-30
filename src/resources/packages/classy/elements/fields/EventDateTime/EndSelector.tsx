import React, { Fragment } from 'react';
import { MouseEventHandler } from 'react';
import DatePicker from '../../components/DatePicker';
import { StartOfWeek } from '../../../types/StartOfWeek';
import { RefObject, useRef } from '@wordpress/element';
import { format } from '@wordpress/date';
import { localizedData } from '../../../localized-data';
import { _x } from '@wordpress/i18n';
import TimePicker from '../../components/TimePicker';

// @todo get this from the tec/classy store.
const timeInterval = localizedData?.settings?.timeInterval ?? 15;

export default function EndSelector( props: {
	dateWithYearFormat: string;
	endDate: Date;
	highlightTime: boolean;
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
		highlightTime,
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
		onChange( 'end', format( 'Y-m-d H:i:s', date ) );
	};

	return (
		<Fragment>
			{ isMultiday && (
				<Fragment>
					<span className="classy-field__separator classy-field__separator--dates">
						{ _x(
							'to',
							'multi-day start and end date separator',
							'the-events-calendar'
						) }
					</span>

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
							onChange={ onChange }
							onClick={ onClick }
							onClose={ onClose }
							onFocusOutside={ onFocusOutside }
							showPopover={ isSelectingDate === 'end' }
							startDate={ startDate }
							startOfWeek={ startOfWeek }
							currentDate={ endDate }
						/>
					</div>
				</Fragment>
			) }

			{ isAllDay ? (
				<span className="classy-field__separator classy-field__separator--dates">
					{ _x(
						'All Day',
						'All day label in the date/time Classy selection field',
						'the-events-calendar'
					) }
				</span>
			) : (
				<div className="classy-field__input classy-field__input--end-time">
					<div className="classy-field__input-title">
						<h4>
							{ _x(
								'End Time',
								'Event end time selection input title',
								'the-events-calendar'
							) }
						</h4>
					</div>

					<TimePicker
						currentDate={ endDate }
						highlight={ highlightTime }
						startDate={ isMultiday ? null : startDate }
						timeFormat={ timeFormat }
						timeInterval={ timeInterval }
						onChange={ onTimeChange }
					/>
				</div>
			) }
		</Fragment>
	);
}
