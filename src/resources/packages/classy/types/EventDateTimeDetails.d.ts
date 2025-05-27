import { Settings } from './LocalizedData';

export type EventDateTimeDetails = {
	eventStart: Date;
	eventEnd: Date;
	isMultiday: boolean;
	isAllDay: boolean;
	eventTimezone: string;
} & Settings;
