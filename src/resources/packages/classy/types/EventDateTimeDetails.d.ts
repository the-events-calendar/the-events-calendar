import { Settings } from '@tec/common/classy/types/LocalizedData';

export type EventDateTimeDetails = {
	eventStart: string;
	eventEnd: string;
	isMultiday: boolean;
	isAllDay: boolean;
	eventTimezone: string;
} & Settings;

export type NewDatesReturn = {
	newStartDate: Date;
	newEndDate: Date;
	notify: {
		startDate: boolean;
		startTime: boolean;
		endDate: boolean;
		endTime: boolean;
	};
};
