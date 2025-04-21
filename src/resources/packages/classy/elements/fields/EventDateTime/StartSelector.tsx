import { StartOfWeek } from '../../../types/StartOfWeek';
import DatePicker from '../../components/DatePicker';
import { Fragment, RefObject, useRef } from '@wordpress/element';
import { __experimentalInputControl as InputControl } from '@wordpress/components';
import { format } from '@wordpress/date';
import { MouseEventHandler } from 'react';

export default function StartSelector( props: {
	dateWithYearFormat: string;
	endDate: Date;
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

	return (
		<Fragment>
			<div
				className="classy-field__input classy-field__input--grow"
				ref={ ref }
			>
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
					show={ isSelectingDate === 'start' }
					startDate={ startDate }
					startOfWeek={ startOfWeek }
					currentDate={ startDate }
				/>
			</div>

			{ ! isAllDay && (
				<div className="classy-field__input">
					<InputControl
						__next40pxDefaultSize
						className="classy-field__control classy-field__control--input classy-field__control--time-picker"
						value={ format( timeFormat, startDate ) }
					/>
				</div>
			) }
		</Fragment>
	);
}
