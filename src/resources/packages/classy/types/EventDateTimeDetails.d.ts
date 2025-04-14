export type EventDateTimeDetails = {
	eventStart: Date;
	eventEnd: Date;
	isMultiday: boolean;
	isAllDay: boolean;
	eventTimezone: string;
	startOfWeek: 0 | 1 | 2 | 3 | 4 | 5 | 6
	dateFormat: string
};
