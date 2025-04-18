import {Fragment, MouseEventHandler} from 'react';
import DatePicker from "../../components/DatePicker";
import {StartOfWeek} from "../../../types/StartOfWeek";
import {RefObject, useRef} from '@wordpress/element';
import {__experimentalInputControl as InputControl} from '@wordpress/components';
import {format} from '@wordpress/date';
import {_x} from '@wordpress/i18n';

export default function EndSelector(props: {
	dateWithYearFormat: string;
	endDate: Date;
	isAllDay: boolean;
	isMultiday: boolean;
	isSelectingDate: 'start' | 'end' | false;
	onClick: MouseEventHandler;
	onClose: () => void;
	onFocusOutside: () => void;
	startDate: Date;
	startOfWeek: StartOfWeek;
	timeFormat: string;
}) {
	const {
		dateWithYearFormat,
		endDate,
		isMultiday,
		isAllDay,
		isSelectingDate,
		onClick,
		onClose,
		onFocusOutside,
		startDate,
		startOfWeek,
		timeFormat,
	} = props;

	const ref: RefObject<HTMLDivElement> = useRef(null);

	return (
		<Fragment>
			{isMultiday && (
				<div className="classy-field__input classy-field__input--grow" ref={ref}>
					<DatePicker
						anchor={ref.current}
						dateWithYearFormat={dateWithYearFormat} endDate={endDate}
						isSelectingDate={isSelectingDate}
						onClick={onClick}
						onClose={onClose}
						onFocusOutside={onFocusOutside}
						show={isSelectingDate === 'end'}
						startDate={startDate}
						startOfWeek={startOfWeek}
						currentDate={endDate}
					/>
				</div>
			)}

			{isAllDay ? (
				<p>
					{_x(
						'All Day',
						'All day label in the date/time Classy selection field',
						'the-events-calendar'
					)}
				</p>
			) : (
				<div className="classy-field__input">
					<InputControl
						__next40pxDefaultSize
						className='classy-field__control classy-field__control--input classy-field__control--time-picker'
						value={format(timeFormat, endDate)}
					/>
				</div>
			)}
		</Fragment>
	);
}
