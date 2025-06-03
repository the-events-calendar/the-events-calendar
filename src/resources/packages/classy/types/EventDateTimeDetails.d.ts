import {Settings} from "@tec/common/classy/types/LocalizedData";

export type EventDateTimeDetails = {
	eventStart: Date;
	eventEnd: Date;
	isMultiday: boolean;
	isAllDay: boolean;
	eventTimezone: string;
} & Settings;
