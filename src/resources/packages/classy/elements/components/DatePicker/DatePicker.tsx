import CalendarPopover from "./CalendarPopover";
import {
	__experimentalInputControl as InputControl,
	__experimentalInputControlSuffixWrapper as SuffixWrapper
} from '@wordpress/components';
import CalendarIcon from "./CalendarIcon";
import {format} from '@wordpress/date';
import {StartOfWeek} from "../../../types/StartOfWeek";
import {Fragment, MouseEventHandler, MutableRefObject} from "react";
import './style.pcss';
import {SyntheticEvent, useRef} from "@wordpress/element";
import { VirtualElement } from "@wordpress/components/build-types/popover/types";

export default function DatePicker(props: {
	anchor:Element | VirtualElement;
	dateWithYearFormat: string;
	endDate: Date;
	isSelectingDate: 'start' | 'end' | false;
	onChange: (selecting: 'start' | 'end', newDate: string) => void;
	onClick: MouseEventHandler<HTMLInputElement>;
	onClose: () => void;
	onFocusOutside: (event: SyntheticEvent) => void;
	show: boolean;
	startDate: Date;
	startOfWeek: StartOfWeek;
	currentDate: Date;
}) {
	const {
		anchor,
		dateWithYearFormat,
		endDate,
		isSelectingDate,
		onChange,
		onClick,
		onClose,
		onFocusOutside,
		show,
		startDate,
		startOfWeek,
		currentDate,
	} = props;

	const input = <InputControl
		__next40pxDefaultSize
		className="classy-field__control classy-field__control--input classy-field__control--date-picker"
		value={format(dateWithYearFormat, currentDate)}
		onClick={onClick}
		suffix={<SuffixWrapper><CalendarIcon/></SuffixWrapper>}
	/>;

	return (
		<Fragment>
			{input}

			{show &&
				<CalendarPopover
					anchor={anchor}
					date={currentDate}
					endDate={endDate}
					isSelectingDate={isSelectingDate as 'start' | 'end'}
					startDate={startDate}
					startOfWeek={startOfWeek}
					onChange={onChange}
					onClose={onClose}
					onFocusOutside={onFocusOutside}
				/>
			}
		</Fragment>
	);
}
