import { Settings } from '@tec/common/classy/types/LocalizedData';

export type EventDateTimeDetails = {
	eventStart: string;
	eventEnd: string;
	isMultiday: boolean;
	isAllDay: boolean;
	eventTimezone: string;
} & Settings;
