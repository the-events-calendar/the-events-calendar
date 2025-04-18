import {StartOfWeek} from "../../../types/StartOfWeek";
import {DatePicker, Popover} from '@wordpress/components';
import {DatePickerEvent} from '@wordpress/components/build-types/date-time/types';
import { VirtualElement } from "@wordpress/components/build-types/popover/types";
import { SyntheticEvent } from "@wordpress/element";

function getDatePickerEventsBetweenDates(start: Date, end: Date): DatePickerEvent[] {
	const dateArray: Date[] = [];
	let currentDate = new Date(start);
	while (currentDate <= end) {
		dateArray.push(new Date(currentDate));
		currentDate.setDate(currentDate.getDate() + 1);
	}

	return dateArray.map((date: Date): DatePickerEvent => {
		return {date};
	});
}

export default function CalendarPopover(props: {
	anchor: Element | VirtualElement;
	startOfWeek: StartOfWeek;
	isSelectingDate: 'start' | 'end';
	date: Date;
	startDate: Date;
	endDate: Date;
	onClose: ()=>void;
	onFocusOutside: (event: SyntheticEvent) => void;
}) {
	const {
		anchor,
		startOfWeek,
		isSelectingDate,
		date,
		startDate,
		endDate,
		onClose,
		onFocusOutside,
	} = props;

	const events = getDatePickerEventsBetweenDates(startDate, endDate);

	return (
		<Popover
			anchor={anchor}
			className="classy-component_popover classy-component_popover--calendar"
			expandOnMobile={true}
			placement="bottom"
			noArrow={false}
			offset={4}
			onClose={onClose}
			onFocusOutside={onFocusOutside}
		>
			<DatePicker
				startOfWeek={startOfWeek}
				currentDate={date}
				onChange={(newDate: string): void =>
					// @ts-ignore If we're here, then `isSelectingDate` will either be `start` or `end`.
					onDateChange(isSelectingDate, newDate)
				}
				events={events}
			/>
		</Popover>
	);
}
